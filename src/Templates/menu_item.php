<?php

use Core\Foundation\Template\Template;
use Modules\Opx\Menu\Models\Menu;

/**
 * HELP:
 *
 * ID parameter is shorthand for defining module and field name separated by `::`.
 * [$module, $name] = explode('::', $id, 2);
 * $captionKey = "{$module}::template.section_{$name}";
 *
 * PLACEMENT is shorthand for section and group of field separated by `/`.
 * [$section, $group] = explode('/', $placement);
 *
 * PERMISSIONS is shorthand for read permission and write permission separated by `|`.
 * [$readPermission, $writePermission] = explode('|', $permissions, 2);
 */

return [
    'sections' => [
        Template::section('general'),
    ],
    'groups' => [
        Template::group('common'),
        Template::group('publication'),
        Template::group('timestamps'),
    ],
    'fields' => [

        // id
        Template::id('id', '/common', 'fields.id_info'),
        // name
        Template::string('name', '/common', '', ['counter' => ['max' => 100]], '', 'required|max:100'),
        // menu id
        Template::select('opx_menu::menu_id', '/common', '', Template::makeList(Menu::class, true), true, '', 'required', '', ['needs_reload' => true]),
        // parent id
        Template::nestedSelect('parent_id', '/common', '', [], true, '', 'required'),
        // order
        Template::string('order', '/common'),
        // url
        Template::string('url', '/common', '', [], '', 'required'),
        // class
        Template::string('class', '/common'),
        // icon
        Template::string('icon', '/common'),
        // new_window
        Template::checkbox('new_window', '/common'),

        // publication
        Template::publicationPublished('/publication'),
        Template::publicationPublishStart('/publication'),
        Template::publicationPublishEnd('/publication'),

        // timestamps
        Template::timestampCreatedAt('/timestamps'),
        Template::timestampUpdatedAt('/timestamps'),
        Template::timestampDeletedAt('/timestamps'),
    ],
];
