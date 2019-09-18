<?php

use Core\Foundation\Template\Template;

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
        Template::group('timestamps'),
    ],
    'fields' => [

        // id
        Template::id('id', '/common', 'fields.id_info'),
        // name
        Template::string('name', '/common', '', ['counter' => ['max' => 100]], '', 'required|max:100'),
        // alias
        Template::string('alias', '/common', '', ['counter' => ['max' => 100]], '', 'required|alpha_dash|max:100'),
        // class
        Template::string('class', '/common'),

        // timestamps
        Template::timestampCreatedAt('/timestamps'),
        Template::timestampUpdatedAt('/timestamps'),
        Template::timestampDeletedAt('/timestamps'),
    ],
];
