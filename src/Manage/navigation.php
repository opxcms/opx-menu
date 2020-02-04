<?php

return [
    'items' => [
        'menus' => [
            'caption' => 'opx_menu::manage.menu',
            'section' => 'system/site',
            'route' => 'opx_menu::menu_list',
            'permission' => 'opx_menu::list',
        ],
        'menu_items' => [
            'caption' => 'opx_menu::manage.menu_items',
            'route' => 'opx_menu::menu_items_list',
            'parent' => 'menus',
            'permission' => 'opx_menu::list',
        ],
    ],

    'routes' => [
        'opx_menu::menu_list' => [
            'route' => '/menu',
            'loader' => 'manage/api/module/opx_menu/menu_list',
        ],
        'opx_menu::menu_add' => [
            'route' => '/menu/add',
            'loader' => 'manage/api/module/opx_menu/menu_edit/add',
        ],
        'opx_menu::menu_edit' => [
            'route' => '/menu/edit/:id',
            'loader' => 'manage/api/module/opx_menu/menu_edit/edit',
        ],
        'opx_menu::menu_items_list' => [
            'route' => '/menu/items',
            'loader' => 'manage/api/module/opx_menu/menu_items_list',
        ],
        'opx_menu::menu_items_add' => [
            'route' => '/menu/items/add',
            'loader' => 'manage/api/module/opx_menu/menu_items_edit/add',
        ],
        'opx_menu::menu_items_edit' => [
            'route' => '/menu/items/edit/:id',
            'loader' => 'manage/api/module/opx_menu/menu_items_edit/edit',
        ],
    ]
];