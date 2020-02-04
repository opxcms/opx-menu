<?php

namespace Modules\Opx\Menu\Controllers;

use Carbon\Carbon;
use Core\Foundation\Templater\Templater;
use Core\Http\Controllers\APIFormController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Authorization\AdminAuthorization;
use Modules\Opx\Menu\Models\MenuItem;
use Modules\Opx\Menu\OpxMenu;

class ManageMenuItemsEditApiController extends APIFormController
{
    public $addCaption = 'opx_menu::manage.add_menu_item';
    public $editCaption = 'opx_menu::manage.edit_menu_item';
    public $create = 'manage/api/module/opx_menu/menu_items_edit/create';
    public $save = 'manage/api/module/opx_menu/menu_items_edit/save';
    public $redirect = '/menu/items/edit/';

    /**
     * Make item add form.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function getAdd(Request $request): JsonResponse
    {
        if(!AdminAuthorization::can('opx_menu::item_add')) {
            return $this->returnNotAuthorizedResponse();
        }

        $menuId = $request->input('scope');
        $parentId = $request->input('parent_id', 0);

        if ($request->has('menu_id')) {
            $menuId = $request->input('menu_id');
        }

        $template = new Templater(OpxMenu::getTemplateFileName('menu_item.php'));

        $template->fillDefaults();
        $template->setValues(['menu_id' => $menuId, 'parent_id' => $parentId]);

        if ($menuId !== null) {
            $template = $this->makeParentOptions($template, $menuId);
        }

        return $this->responseFormComponent(0, $template, $this->addCaption, $this->create);
    }

    /**
     * Make item edit form.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function getEdit(Request $request): JsonResponse
    {
        if(!AdminAuthorization::can('opx_menu::item_edit')) {
            return $this->returnNotAuthorizedResponse();
        }

        $id = $request->input('id');

        /** @var MenuItem $item */
        $item = MenuItem::where('id', $id)->firstOrFail();

        if ($request->has('menu_id')) {
            $item->setAttribute('menu_id', $request->input('menu_id', 0));
        }

        $template = $this->makeTemplate($item, 'menu_item.php');

        return $this->responseFormComponent($id, $template, $this->editCaption, $this->save);
    }

    /**
     * Fill available parents for current menu.
     *
     * @param Templater $template
     * @param $menuId
     *
     * @return  Templater
     */
    protected function makeParentOptions(Templater $template, $menuId): Templater
    {
        $field = $template->getField('parent_id');
        $parents = MenuItem::where('menu_id', $menuId)->get(['id', 'parent_id', 'name as caption'])->toArray();
        $field['options'] = $parents;
        $template->setField('parent_id', $field);

        return $template;
    }

    /**
     * Fill template with data.
     *
     * @param string $filename
     * @param MenuItem $item
     *
     * @return  Templater
     */
    protected function makeTemplate(MenuItem $item, $filename): Templater
    {
        $template = new Templater(OpxMenu::getTemplateFileName($filename));

        $template->fillValuesFromObject($item);

        if ($menuId = $item->getAttribute('menu_id')) {
            $template = $this->makeParentOptions($template, $menuId);
        }

        return $template;
    }

    /**
     * Create or reload new item.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function postCreate(Request $request): JsonResponse
    {
        if(!AdminAuthorization::can('opx_menu::item_add')) {
            return $this->returnNotAuthorizedResponse();
        }

        if ($request->input('__reload') === true) {
            return $this->getAdd($request);
        }

        $template = new Templater(OpxMenu::getTemplateFileName('menu_item.php'));

        $template->resolvePermissions();

        $template->fillValuesFromRequest($request);

        if (!$template->validate()) {
            return $this->responseValidationError($template->getValidationErrors());
        }

        $values = $template->getEditableValues();

        $item = $this->updateItemData(new MenuItem(), $values);

        // Refill template
        $template = $this->makeTemplate($item, 'menu_item.php');
        $id = $item->getAttribute('id');

        return $this->responseFormComponent($id, $template, $this->editCaption, $this->save, $this->redirect . $id);
    }

    /**
     * Create or reload new item.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function postSave(Request $request): JsonResponse
    {
        if(!AdminAuthorization::can('opx_menu::item_edit')) {
            return $this->returnNotAuthorizedResponse();
        }

        if ($request->input('__reload') === true) {
            return $this->getEdit($request);
        }

        $id = $request->input('id');

        /** @var MenuItem $item */
        $item = MenuItem::where('id', $id)->firstOrFail();

        $template = $template = $this->makeTemplate($item, 'menu_item.php');

        $template->resolvePermissions();

        $template->fillValuesFromRequest($request);

        if (!$template->validate()) {
            return $this->responseValidationError($template->getValidationErrors());
        }

        $values = $template->getEditableValues();

        $item = $this->updateItemData($item, $values);

        // Refill template
        $template = $this->makeTemplate($item, 'menu_item.php');
        $id = $item->getAttribute('id');

        return $this->responseFormComponent($id, $template, $this->editCaption, $this->save);
    }

    /**
     * Store data to item.
     *
     * @param MenuItem $item
     * @param $values
     *
     * @return  MenuItem
     */
    protected function updateItemData(MenuItem $item, $values): MenuItem
    {
        $this->setAttributes($item, $values, [
            'name',
            'menu_id', 'parent_id',
            'url', 'class', 'icon', 'new_window',
            'order',
            'published', 'publish_start', 'publish_end',
        ]);

        $item->save();

        return $item;
    }
}