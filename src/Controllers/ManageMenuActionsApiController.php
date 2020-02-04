<?php

namespace Modules\Opx\Menu\Controllers;

use Core\Http\Controllers\ApiActionsController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Modules\Opx\Menu\Models\Menu;

class ManageMenuActionsApiController extends ApiActionsController
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
        return $this->deleteModels(Menu::class, $request->all(), 'opx_menu::delete');
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
        return $this->restoreModels(Menu::class, $request->all(), 'opx_menu::delete');
    }
}