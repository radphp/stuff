<?php

namespace Rad\Stuff\Admin;

use Rad\Authentication\Auth;
use Rad\Authorization\Rbac;
use Rad\DependencyInjection\Container;
use Rad\Events\EventManagerTrait;

class Menu
{
    use EventManagerTrait;

    private $label = '';
    private $icon = '';
    private $link = '#';
    private $order = 100;
    private $children = [];
    private $resources = [];

    /** @var array $menu admin menu */
    private static $menu = [];

    const EVENT_GET_MENU = 'bundles.admin.getMenu';

    /**
     * Generate menu, it's only used in this bundle!
     *
     * @return string Menu HTML code
     *
     * @todo return must be array, these HTML codes may be in twig template
     */
    public static function generate()
    {
        self::dispatchEvent(self::EVENT_GET_MENU);

        $return = '';

        // sort menu by it's order
        uksort(self::$menu, 'strnatcmp');;

        /** @var Menu $item */
        foreach (self::$menu as $item) {
            if ($item->resources && !self::userHasResource($item->resources)) {
                continue;
            }

            if ($children = $item->getChildren()) {
                $subItems = '';

                // sort menu by it's order
                uksort($children, 'strnatcmp');;

                /** @var Menu $child */
                foreach ($children as $child) {
                    if ($child->resources && !self::userHasResource($child->resources)) {
                        continue;
                    }

                    $subItems .= '<li><a href="' . $child->getLink() . '">' . $child->getLabel() . '</a></li>';
                }

                $return .= '<li class="treeview">
                  <a href="#">
                    <i class="fa ' . $item->getIcon() . '"></i>
                    <span>' . $item->getLabel() . '</span>
                    <i class="fa fa-angle-left pull-right"></i>
                  </a>
                  <ul class="treeview-menu">
                    ' . $subItems . '
                  </ul>
                </li>';
            } else {
                $return .= '<li>
                        <a href="' . $item->getLink() . '">
                            <i class="fa ' . $item->getIcon() . ' fa-fw"></i> <span>' . $item->getLabel() . '</span>
                        </a>
                    </li>';
            }
        }

        return $return;
    }

    /**
     * Set label
     *
     * @param $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set icon
     *
     * @param $icon
     *
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set link
     *
     * @param $link
     *
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Set order
     *
     * @param $order
     *
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Add child to menu
     *
     * @param Menu $child
     *
     * @return $this
     */
    public function addChild(Menu $child)
    {
        $this->children[$child->getOrder() . '--' . $child->getLabel()] = $child;

        return $this;
    }

    /**
     * Get all children
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Is this menu "root" or not
     * @return $this
     */
    public function setAsRoot()
    {
        self::$menu[$this->order . '--' . $this->label] = $this;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Get order
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Get resources
     *
     * @return array
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Set resources
     *
     * @param array $resources
     *
     * @return $this
     */
    public function setResources($resources)
    {
        $this->resources = $resources;

        return $this;
    }

    /**
     * If user has at least ONE resource of the resources, return TRUE
     * @param $resources
     *
     * @return bool
     */
    private static function userHasResource($resources)
    {
        if (!is_array($resources)) {
            $resources = [$resources];
        }

        $container = Container::getInstance();

        /** @var Rbac $rbac */
        $rbac = $container->get('rbac');

        /** @var Auth $auth */
        $auth = $container->get('auth');

        foreach($resources as $resource){
            foreach ($auth->getStorage()->read()['roles'] as $roleName) {
                if (true === $rbac->isGranted($roleName, $resource)) {
                    return true;
                }
            }
        }

        return false;
    }
}
