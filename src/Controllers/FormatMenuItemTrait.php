<?php

namespace Modules\Opx\Menu\Controllers;

use Modules\Opx\Menu\Models\MenuItem;

trait FormatMenuItemTrait
{
    /**
     * Format user record for displaying in list.
     *
     * @param MenuItem $item
     *
     * @return  array
     */
    protected function formatMenuItem(MenuItem $item): array
    {
        $childrenCount = $item->getAttribute('children_count');

        $props = [];
        $props[] = "order: {$item->getAttribute('order')}";
        if (($class = $item->getAttribute('class')) !== null) {
            $props[] = "class: {$class}";
        }
        if (($icon = $item->getAttribute('icon')) !== null) {
            $props[] = "icon: {$icon}";
        }
        if ($item->getAttribute('publish_start') !== null) {
            $props[] = trans('manage.publish_start') . ': ';
            $props[] = 'datetime:' . $item->getAttribute('publish_start')->toIso8601String();
        }
        if ($item->getAttribute('publish_end') !== null) {
            $props[] = trans('manage.publish_end') . ': ';
            $props[] = 'datetime:' . $item->getAttribute('publish_end')->toIso8601String();
        }

        return $this->makeListRecord(
            $item->getAttribute('id'),
            $item->getAttribute('name'),
            null,
            $item->getAttribute('url'),
            $props,
            $item->isPublished(),
            $item->getAttribute('deleted_at') !== null,
            $childrenCount
        );
    }
}