<?php

namespace Modules\Opx\Menu;

use Illuminate\Support\Facades\Facade;

/**
 * @method  static string getTemplateFileName(string $name)
 * @method  static string|null renderMenu($menuAlias, $class = null, $maxLevels = 0, $clean = false)
 * @method  static array getAsArray($menuAlias, $maxLevels = 0)
 * @method  static string getAsJson($menuAlias, $maxLevels = 0)
 */
class OpxMenu extends Facade
{
    /**
     * Get the registered name of the component.
     *
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'opx_menu';
    }
}
