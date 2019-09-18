<?php

namespace Modules\Opx\Menu\Controllers;

use Core\Http\Controllers\APIListController;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Modules\Opx\Menu\Models\MenuItem;

class ManageMenuItemsActionsApiController extends APIListController
{
    use FormatMenuItemTrait;

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

        /** @var EloquentBuilder $items */
        $items = MenuItem::query()->whereIn('id', $ids)->get();

        if ($items->count() > 0) {
            /** @var MenuItem $item */
            foreach ($items as $item) {
                $item->delete();
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

        /** @var EloquentBuilder $items */
        $items = MenuItem::query()->whereIn('id', $ids)->onlyTrashed()->get();

        if ($items->count() > 0) {
            /** @var MenuItem $item */
            foreach ($items as $item) {
                $item->restore();
            }
        }

        return response()->json(['message' => 'success']);
    }


    /**
     * Publish items with given ids and clear publishing limitation dates if need.
     * Returns response with corrected pages.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function postEnable(Request $request): JsonResponse
    {
        $ids = $request->all();

        /** @var EloquentBuilder $items */
        $items = MenuItem::query()->withCount('children')->whereIn('id', $ids)->get();

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
        $ids = $request->all();

        /** @var EloquentBuilder $items */
        $items = MenuItem::query()->whereIn('id', $ids)->get();

        if ($items->count() > 0) {
            /** @var MenuItem $item */
            foreach ($items as $item) {
                if ($item->isPublished()) {
                    $item->unPublish();
                    $item->save();
                }
            }
        }

        return response()->json([
            'message' => 'success',
        ]);
    }
}