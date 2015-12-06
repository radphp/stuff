<?php

namespace Rad\Stuff\Admin;

use Rad\Events\EventManagerTrait;

class Menu
{
    use EventManagerTrait;

    private $label = '';
    private $icon = '';
    private $link = '#';
    private $order = 100;
    private $children = [];

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
            if ($children = $item->getChildren()) {
                $subItems = '';

                // sort menu by it's order
                uksort($children, 'strnatcmp');;

                /** @var Menu $child */
                foreach ($children as $child) {
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
}
