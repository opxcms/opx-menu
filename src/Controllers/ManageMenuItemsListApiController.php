<?php

namespace Modules\Opx\Menu\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Core\Http\Controllers\APIListController;
use Modules\Admin\Authorization\AdminAuthorization;
use Modules\Opx\Menu\Models\Menu;
use Modules\Opx\Menu\Models\MenuItem;

class ManageMenuItemsListApiController extends APIListController
{
    use FormatMenuItemTrait;

    protected $caption = 'opx_menu::manage.menu_items';
    protected $source = 'manage/api/module/opx_menu/menu_items_list/items';
    protected $children = true;

    protected $filters = [
        'show_all' => [
            'caption' => 'filters.filter_by_show_all',
            'type' => 'switch',
            'enabled' => false,
            'value' => true,
        ],
        'published' => [
            'caption' => 'filters.filter_by_published',
            'type' => 'checkbox',
            'enabled' => false,
            'value' => 'published',
            'options' => ['published' => 'filters.filter_value_published', 'unpublished' => 'filters.filter_value_unpublished'],
        ],
        'show_deleted' => [
            'caption' => 'filters.filter_by_deleted',
            'type' => 'checkbox',
            'enabled' => false,
            'value' => 'deleted',
            'options' => ['deleted' => 'filters.filter_value_deleted', 'only_deleted' => 'filters.filter_value_only_deleted'],
        ],
    ];

    protected $order = [
        'current' => 'order',
        'direction' => 'asc',
        'fields' => [
            'id' => 'orders.sort_by_id',
            'order' => 'orders.sort_by_order',
            'name' => 'orders.sort_by_name',
            'creation_date' => 'orders.sort_by_creation_date',
            'update_date' => 'orders.sort_by_update_date',
            'delete_date' => 'orders.sort_by_delete_date',
        ],
    ];

    protected $search = [
        'id' => [
            'caption' => 'search.search_by_id',
            'default' => true,
        ],
        'name' => [
            'caption' => 'search.search_by_name',
            'default' => true,
        ],
    ];

    /**
     * Get list of users with sorting, filters and search.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function postItems(Request $request): JsonResponse
    {
        if (!AdminAuthorization::can('opx_menu::list')) {
            return $this->returnNotAuthorizedResponse();
        }

        $order = $request->input('order');
        $filters = $request->input('filters');
        $search = $request->input('search');

        if (empty($filters['show_all'])) {
            $parentId = $request->input('parent_id', 0);
        } else {
            $parentId = null;
        }
        $scope = $request->input('scope');

        $items = $this->makeQuery($parentId, $scope);

        $items = $this->applyOrder($items, $order);
        $items = $this->applyFilters($items, $filters);
        $items = $this->applySearch($items, $search);

        $items = $items->paginate(50);

        /** @var Collection $items */
        if ($items->count() > 0) {
            $items->transform(function ($item) {
                /** @var MenuItem $item */
                return $this->formatMenuItem($item);
            });
        }

        $response = $items->toArray();

        if (!empty($parentId)) {
            /** @var MenuItem $parent */
            $parent = MenuItem::withTrashed()->where('id', $parentId)->first();
            if ($parent !== null) {
                $response['parent'] = $parent->getAttribute('parent_id');
                $response['description'] = $parent->getAttribute('name');
            }
        }

        return response()->json($response);
    }

    /**
     * Make base list query.
     *
     * @param int|null $parentId
     * @param int|null $scopeId
     *
     * @return  EloquentBuilder
     */
    protected function makeQuery(int $parentId = null, int $scopeId = null): EloquentBuilder
    {
        /** @var EloquentBuilder $query */
        $query = MenuItem::query()->select('menu_items.*')->withCount('children');
        $query->when($scopeId !== null, static function ($query) use ($scopeId) {
            /** @var EloquentBuilder $query */
            $query->where('menu_id', $scopeId);
        });
        $query->when($parentId !== null, static function ($query) use ($parentId) {
            /** @var EloquentBuilder $query */
            $query->where('parent_id', $parentId);
        });

        return $query;
    }

    /**
     * Apply order to query.
     *
     * @param EloquentBuilder $query
     * @param array $order
     *
     * @return  EloquentBuilder
     */
    protected function applyOrder(EloquentBuilder $query, $order): EloquentBuilder
    {
        $direction = $order['direction'];
        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = $this->order['direction'];
        }

        switch ($order['by'] ?? '') {
            case 'id':
                $query->orderBy('id', $direction);
                break;
            case 'name':
                $query->orderBy('name', $direction);
                break;
            case 'creation_date':
                $query->orderByRaw('ISNULL(created_at) asc')->orderBy('created_at', $direction);
                break;
            case 'update_date':
                $query->orderByRaw('ISNULL(updated_at) asc')->orderBy('updated_at', $direction);
                break;
            case 'delete_date':
                $query->orderByRaw('ISNULL(deleted_at) asc')->orderBy('deleted_at', $direction);
                break;
            case 'published':
                $now = Carbon::now()->toDateTimeString();
                $query->orderByRaw("IF(published = 1 AND (ISNULL(publish_start) OR STR_TO_DATE('{$now}', '%Y-%m-%d %H:%i:%s') > publish_start) AND (ISNULL(publish_end) OR STR_TO_DATE('{$now}', '%Y-%m-%d %H:%i:%s') < publish_end), 0, 1) {$direction}");
                break;
            case 'publish_start':
                $query->orderByRaw('ISNULL(publish_start) asc')->orderBy('publish_start', $direction);
                break;
            case 'publish_end':
                $query->orderByRaw('ISNULL(publish_end) asc')->orderBy('publish_end', $direction);
                break;
            case 'order':
            default:
                $query->orderByRaw('ISNULL(`order`) asc')->orderBy('order', $direction);
        }
        return $query;
    }

    /**
     * Apply filters to query.
     *
     * @param EloquentBuilder $query
     * @param array $filters
     *
     * @return  EloquentBuilder
     */
    protected function applyFilters(EloquentBuilder $query, $filters): EloquentBuilder
    {
        if (isset($filters['published'])) {
            $now = Carbon::now()->toDateTimeString();
            $show = $filters['published'] === 'published' ? 0 : 1;
            $query->whereRaw("IF(published = 1 AND (ISNULL(publish_start) OR STR_TO_DATE('{$now}', '%Y-%m-%d %H:%i:%s') > publish_start) AND (ISNULL(publish_end) OR STR_TO_DATE('{$now}', '%Y-%m-%d %H:%i:%s') < publish_end), 0, 1) = {$show}");
        }
        if (isset($filters['show_deleted'])) {
            if ($filters['show_deleted'] === 'deleted') {
                $query->withTrashed();
            } elseif ($filters['show_deleted'] === 'only_deleted') {
                $query->onlyTrashed();
            }
        }
        return $query;
    }

    /**
     * Apply search to query.
     *
     * @param EloquentBuilder $query
     * @param array $search
     *
     * @return  EloquentBuilder
     */
    protected function applySearch(EloquentBuilder $query, $search): EloquentBuilder
    {
        if (!empty($search['subject']) && !empty($search['fields'])) {

            $subject = str_replace('*', '%', $search['subject']);
            $fields = explode(',', $search['fields']);

            $query = $query->where(static function ($q) use ($fields, $subject) {
                /** @var Builder $q */
                if (in_array('id', $fields, true)) {
                    $q->orWhere('id', 'LIKE', $subject);
                }
                if (in_array('name', $fields, true)) {
                    $q->orWhere('name', 'LIKE', $subject);
                }
            });
        }
        return $query;
    }

    /**
     * Make quick navigation.
     *
     * @return  array
     */
    protected function makeQuickNav(): array
    {
        /** @var Collection $models */
        $menus = Menu::query()->orderBy('name')->selectRaw('id, 0 as parent_id, name as caption')->get();

        return $menus->toArray();
    }

    /**
     * Get add link.
     *
     * @return  string
     */
    protected function getAddLink(): ?string
    {
        return AdminAuthorization::can('opx_menu::item_add') ? 'opx_menu::menu_items_add' : null;
    }

    /**
     * Get edit link.
     *
     * @return  string
     */
    protected function getEditLink(): ?string
    {
        return AdminAuthorization::can('opx_menu::item_edit') ? 'opx_menu::menu_items_edit' : null;
    }

    /**
     * Get edit link.
     *
     * @return  string
     */
    protected function getEnableLink(): ?string
    {
        return AdminAuthorization::can('opx_menu::item_disable') ? 'manage/api/module/opx_menu/menu_items_actions/enable' : null;
    }

    /**
     * Get edit link.
     *
     * @return  string
     */
    protected function getDisableLink(): ?string
    {
        return AdminAuthorization::can('opx_menu::item_disable') ? 'manage/api/module/opx_menu/menu_items_actions/disable' : null;
    }

    /**
     * Get edit link.
     *
     * @return  string
     */
    protected function getDeleteLink(): ?string
    {
        return AdminAuthorization::can('opx_menu::item_delete') ? 'manage/api/module/opx_menu/menu_items_actions/delete' : null;
    }

    /**
     * Get edit link.
     *
     * @return  string
     */
    protected function getRestoreLink(): ?string
    {
        return AdminAuthorization::can('opx_menu::item_delete') ? 'manage/api/module/opx_menu/menu_items_actions/restore' : null;
    }
}