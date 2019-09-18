<?php

namespace Modules\Opx\Menu;

use Core\Foundation\Module\BaseModule;
use Modules\Opx\Menu\Helpers\MenuBuilder;

class Menu extends BaseModule
{
    /**
     * Get rendered menu.
     *
     * @param string $menuAlias
     * @param null|string $class
     * @param null|bool|string $iconClass
     * @param integer $maxLevels
     * @param boolean $clean
     *
     * @return  null|string
     */
    public function renderMenu($menuAlias, $class = null, $iconClass= null, $maxLevels = 0, $clean = false): ?string
    {
        return MenuBuilder::render($menuAlias, $class, $iconClass, $maxLevels, $clean);
    }

    /**
     * Get menu as array.
     *
     * @param string $menuAlias
     * @param integer $maxLevels
     *
     * @return  array
     */
    public function getAsArray($menuAlias, $maxLevels = 0): array
    {
        return MenuBuilder::asArray($menuAlias, $maxLevels);
    }

    /**
     * Get menu as JSON.
     *
     * @param string $menuAlias
     * @param integer $maxLevels
     *
     * @return  string
     */
    public function getAsJson($menuAlias, $maxLevels = 0): string
    {
        return json_encode(MenuBuilder::asArray($menuAlias, $maxLevels));
    }
}
