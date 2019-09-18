<?php

namespace Modules\Opx\Menu\Controllers;

use Core\Http\Controllers\APIListController;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Modules\Opx\Menu\Models\Menu;

class ManageMenuActionsApiController extends APIListController
{
    /**
     * Delete items with given ids.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function postDelete(Request $request): JsonResponse
    {
        $ids = $request->all();

        /** @var EloquentBuilder $menus */
        $menus = Menu::query()->whereIn('id', $ids)->get();

        if ($menus->count() > 0) {
            /** @var Menu $menu */
            foreach ($menus as $menu) {
                $menu->delete();
            }
        }

        return response()->json(['message' => 'success']);
    }

    /**
     * Restore items with given ids.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function postRestore(Request $request): JsonResponse
    {
        $ids = $request->all();

        /** @var EloquentBuilder $menus */
        $menus = Menu::query()->whereIn('id', $ids)->onlyTrashed()->get();

        if ($menus->count() > 0) {
            /** @var Menu $menu */
            foreach ($menus as $menu) {
                $menu->restore();
            }
        }

        return response()->json(['message' => 'success']);
    }
}