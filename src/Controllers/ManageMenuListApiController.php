<?php

namespace Modules\Opx\Menu\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Core\Http\Controllers\APIListController;
use Modules\Admin\Authorization\AdminAuthorization;
use Modules\Opx\Menu\Models\Menu;

class ManageMenuListApiController extends APIListController
{
    protected $caption = 'opx_menu::manage.menu';
    protected $source = 'manage/api/module/opx_menu/menu_list/menu';
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
        if (!AdminAuthorization::can('opx_menu::list')) {
            return $this->returnNotAuthorizedResponse();
        }

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

    /**
     * Get add link.
     *
     * @return  string
     */
    protected function getAddLink(): ?string
    {
        return AdminAuthorization::can('opx_menu::add') ? 'opx_menu::menu_add' : null;
    }

    /**
     * Get edit link.
     *
     * @return  string
     */
    protected function getEditLink(): ?string
    {
        return AdminAuthorization::can('opx_menu::edit') ? 'opx_menu::menu_edit' : null;
    }

    /**
     * Get edit link.
     *
     * @return  string
     */
    protected function getDeleteLink(): ?string
    {
        return AdminAuthorization::can('opx_menu::delete') ? 'manage/api/module/opx_menu/menu_actions/delete' : null;
    }

    /**
     * Get edit link.
     *
     * @return  string
     */
    protected function getRestoreLink(): ?string
    {
        return AdminAuthorization::can('opx_menu::delete') ? 'manage/api/module/opx_menu/menu_actions/restore' : null;
    }

}