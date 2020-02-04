<?php

namespace Modules\Opx\Menu\Helpers;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Collection;
use Modules\Opx\Menu\Models\Menu;
use Modules\Opx\Menu\Models\MenuItem;

class MenuBuilder
{
    /**
     * Render menu.
     *
     * @param string $alias
     * @param string|null $class
     * @param string|bool|null $iconClass
     * @param integer $limit
     * @param boolean $clean
     *
     * @return  null|string
     */
    public static function render($alias, $class = null, $iconClass = null, $limit = 0, $clean = false): ?string
    {
        $startTime = microtime(true);

        /** @var Menu $menu */
        $menu = Menu::query()->where('alias', $alias)->first();

        if ($menu === null) {
            return null;
        }

        $items = self::getMenuItems($menu);

        if ($items === null || $items === []) {
            return null;
        }

        $tree = self::buildTree($items, $limit);

        $tree = self::markCurrentActive($tree);

        $rendered = "<!-- menu '$alias' limit: $limit -->" . "\r\n";

        $rendered .= self::renderMenu($menu, $tree, $class, $iconClass, $clean);

        $rendered .= "<!-- end of menu '{$alias}' limit: {$limit} rendered at " . ((microtime(true) - $startTime) * 1000) . ' ms -->' . "\r\n";

        return $rendered;

    }

    /**
     * Get all published items for current menu.
     *
     * @param Menu $menu
     *
     * @return  array|null
     */
    protected static function getMenuItems(Menu $menu): ?array
    {
        // Get items for the menu.
        /** @var Collection $items */
        $items = MenuItem::query()->where('menu_id', $menu->getAttribute('id'))
            ->where('published', 1)
            ->where(static function ($query) {
                /** @var Builder $query */
                $query
                    ->whereNull('publish_start')
                    ->orWhere('publish_start', '<=', Carbon::now());
            })
            ->where(static function ($query) {
                /** @var Builder $query */
                $query
                    ->whereNull('publish_end')
                    ->orWhere('publish_end', '>=', Carbon::now());
            })
            ->orderByRaw('ISNULL(`order`) asc')
            ->orderBy('order')
            ->get();

        // Remove unnecessary fields and transform URLs
        $items = $items
            ->map(static function ($item) {
                /** @var MenuItem $item */

                // check if url is route name
                $url = $item->getAttribute('url');
                if (strpos($url, '::') !== false && Route::has($url)) {
                    $url = route($url, [], false);
                }

                $url = preg_replace('/^\/+/', '/', "/{$url}");

                return [
                    'id' => $item->getAttribute('id'),
                    'parent_id' => $item->getAttribute('parent_id'),
                    'name' => $item->getAttribute('name'),
                    'url' => $url,
                    'class' => $item->getAttribute('class'),
                    'icon' => $item->getAttribute('icon'),
                    'new_window' => $item->getAttribute('new_window'),
                ];
            })
            ->groupBy('parent_id')
            ->toArray();

        return $items;
    }

    /**
     * Build tree.
     *
     * @param array $items
     * @param integer $limit
     *
     * @return  null|array
     */
    protected static function buildTree(array $items, $limit): ?array
    {
        return self::buildTreeNode(0, $items, 0, $limit);
    }

    /**
     * Recursive build menu tree.
     *
     * @param integer $parent_id
     * @param array $items
     * @param integer $level
     * @param integer $limit
     *
     * @return  null|array
     */
    protected static function buildTreeNode($parent_id, $items, $level, $limit): ?array
    {
        if ($limit !== 0 && $level === $limit) {
            return null;
        }

        if (!isset($items[$parent_id]) || $items[$parent_id] === []) {
            return null;
        }

        $result = [];
        $level++;

        foreach ($items[$parent_id] as $item) {
            $item['level'] = $level;
            $item['current'] = false;
            $item['active'] = false;
            $item['has_children'] = false;
            if (isset($items[$item['id']]) && $items[$item['id']] !== []) {
                $item['has_children'] = true;
                $item['items'] = self::buildTreeNode($item['id'], $items, $level, $limit);
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Mark active and current items in menu.
     *
     * @param array $items
     *
     * @return  array
     */
    protected static function markCurrentActive(array $items): ?array
    {
        self::itemHasActive($items, preg_replace('/^\/+/', '/', '/' . request()->path()));

        return $items;
    }

    /**
     * Check if menu item is active.
     *
     * @param array $items
     * @param string $current
     *
     * @return  bool
     */
    protected static function itemHasActive(&$items, $current): bool
    {
        if (count($items) > 0) {
            foreach ($items as $key => $item) {
                if ($item['url'] === $current) {
                    $item['active'] = true;
                    $items[$key] = $item;
                    return true;
                }
                if (isset($item['items']) && self::itemHasActive($item['items'], $current)) {
                    $item['current'] = true;
                    $items[$key] = $item;
                }
            }
        }

        return false;
    }

    /**
     * Render menu.
     *
     * @param Menu $menu
     * @param array $items
     * @param string|null $class
     * @param string|bool|null $iconClass
     * @param boolean $clean
     *
     * @return  string
     */
    protected static function renderMenu(Menu $menu, array $items, $class, $iconClass, $clean): string
    {
        if ($class === null) {
            $class = $menu->getAttribute('class');
        }

        $rendered = ($clean ? '  <ul>' : "  <ul class=\"{$class}\">") . "\r\n";

        $rendered .= self::renderItems($items, $class, $iconClass, $clean);

        $rendered .= "  </ul>\r\n";

        // End of menu wrapper

        return $rendered;
    }

    /**
     * Render menu tree.
     *
     * @param array $items
     * @param string|null $class
     * @param string|bool|null $iconClass
     * @param boolean $clean
     *
     * @return  null|string
     */
    protected static function renderItems(array $items, $class, $iconClass, $clean): ?string
    {
        if (count($items) === 0) {
            return null;
        }
        $result = '';
        $divider = strpos($class, '__') === false ? '__' : '-';

        foreach ($items as $item) {

            $classes = $clean
                ? []
                : [
                    'base' => "{$class}{$divider}item",
                    'active' => $item['active'] ? " {$class}{$divider}item-active" : '',
                    'current' => $item['current'] ? " {$class}{$divider}item-current" : '',
                    'parent' => $item['has_children'] ? " {$class}{$divider}item-parent" : '',
                    'level' => " {$class}{$divider}item-level-" . $item['level'],
                    'item' => " {$item['class']}",
                ];

            $liClass = trim(implode($classes));
            $liClass = " class=\"{$liClass}\"";
            $aClass = $clean ? '' : " class=\"{$class}{$divider}item-link\"";
            $spanClass = $clean ? '' : " class=\"{$class}{$divider}item-link-caption\"";

            $icon = null;

            if ($iconClass !== null && $iconClass !== false && !$clean && $item['icon'] !== null) {
                $icon = "<span class=\"{$class}{$divider}item-link-icon";
                if ($iconClass === true) {
                    $icon .= " {$item['icon']}\"></span>";
                } else {
                    $icon .= " {$iconClass} {$iconClass}-{$item['icon']}\"></span>";
                }
            }

            $newWindow = $item['new_window'] ? ' target="_blank"' : '';

            $result .= str_repeat('  ', ($item['level'] - 1) * 2 + 2) . "<li{$liClass}>";

            if (!empty($item['url'])) {
                $result .= "<a{$aClass} href=\"{$item['url']}\"{$newWindow}>{$icon}<span{$spanClass}>{$item['name']}</span></a>";
            } else {
                $result .= "<span{$aClass}>{$icon}<span{$spanClass}>{$item['name']}</span></span>";
            }
            if (isset($item['items'])) {
                $result .= "\r\n" . str_repeat('  ', ($item['level'] - 1) * 2 + 3) . ($clean ? '' : "<ul class=\"{$class}{$divider}submenu {$classes['level']}\">") . "\r\n";
                $result .= self::renderItems($item['items'], $class, $iconClass, $clean);
                $result .= str_repeat('  ', ($item['level'] - 1) * 2 + 3) . '</ul>' . "\r\n" . str_repeat('  ', ($item['level'] - 1) * 2 + 2);
            }
            $result .= '</li>' . "\r\n";
        }
        return $result;
    }

    /**
     * Get menu as array.
     *
     * @param $alias
     * @param $limit
     *
     * @return array|null
     */
    public static function asArray($alias, $limit): ?array
    {
        /** @var Menu $menu */
        $menu = Menu::query()->where('alias', $alias)->first();

        if ($menu === null) {
            return null;
        }

        $items = self::getMenuItems($menu);

        if ($items === null || $items === []) {
            return [];
        }

        $tree = self::buildTree($items, $limit);

        $tree = self::markCurrentActive($tree);

        return $tree;
    }
}