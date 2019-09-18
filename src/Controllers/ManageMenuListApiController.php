<?php

namespace Modules\Opx\Menu\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Core\Http\Controllers\APIListController;
use Modules\Opx\Menu\Models\Menu;

class ManageMenuListApiController extends APIListController
{
    protected $caption = 'opx_menu::manage.menu';
    protected $source = 'manage/api/module/opx_menu/menu_list/menu';

    protected $delete = 'manage/api/module/opx_menu/menu_actions/delete';
    protected $restore = 'manage/api/module/opx_menu/menu_actions/restore';

    protected $add = 'opx_menu::menu_add';
    protected $edit = 'opx_menu::menu_edit';

    protected $children = false;

    /**
     * Get list of users with sorting, filters and search.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function postMenu(Request $request): JsonResponse
    {
        $menus = Menu::withTrashed()->get();

        /** @var Collection $menus */
        if ($menus->count() > 0) {
            $menus->transform(function ($menu) {
                /** @var Menu $menu */
                return $this->makeListRecord(
                    $menu->getAttribute('id'),
                    $menu->getAttribute('name'),
                    null,
                    null,
                    [$menu->getAttribute('alias')],
                    true,
                    $menu->getAttribute('deleted_at') !== null
                );
            });
        }

        $response = ['data' => $menus->toArray()];

        return response()->json($response);
    }
}