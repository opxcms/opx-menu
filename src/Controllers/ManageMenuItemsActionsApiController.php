<?php

namespace Modules\Opx\Menu\Controllers;

use Core\Http\Controllers\ApiActionsController;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Modules\Admin\Authorization\AdminAuthorization;
use Modules\Opx\Menu\Models\MenuItem;

class ManageMenuItemsActionsApiController extends ApiActionsController
{
    use FormatMenuItemTrait;

    /**
     * Delete items with given ids.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function postDelete(Request $request): JsonResponse
    {
        return $this->deleteModels(MenuItem::class, $request->all(), 'opx_menu::item_delete');
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
        return $this->restoreModels(MenuItem::class, $request->all(), 'opx_menu::item_delete');
    }


    /**
     * Publish items with given ids and clear publishing limitation dates if need.
     * Returns response with corrected items.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function postEnable(Request $request): JsonResponse
    {
        if (!AdminAuthorization::can('opx_menu::disable')) {
            return $this->returnNotAuthorizedResponse();
        }

        /** @var EloquentBuilder $items */
        $items = MenuItem::query()->withCount('children')->whereIn('id', $request->all())->get();

        $changed = [];

        if ($items->count() > 0) {
            /** @var MenuItem $item */
            foreach ($items as $item) {
                if (!$item->isPublished()) {
                    $item->publish();
                    $item->save();
                    $changed[$item->getAttribute('id')] = $this->formatMenuItem($item);
                }
            }
        }

        return response()->json([
            'message' => 'success',
            'changed' => $changed,
        ]);
    }

    /**
     * Mark items as unpublished with given ids.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function postDisable(Request $request): JsonResponse
    {
        return $this->disableModels(MenuItem::class, $request->all(), 'published', 'opx_menu::disable');
    }
}