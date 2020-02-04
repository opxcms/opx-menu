<?php

namespace Modules\Opx\Menu\Controllers;

use Core\Foundation\Templater\Templater;
use Core\Http\Controllers\APIFormController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Admin\Authorization\AdminAuthorization;
use Modules\Opx\Menu\Models\Menu;
use Modules\Opx\Menu\OpxMenu;

class ManageMenuEditApiController extends APIFormController
{
    public $addCaption = 'opx_menu::manage.add_menu';
    public $editCaption = 'opx_menu::manage.edit_menu';
    public $create = 'manage/api/module/opx_menu/menu_edit/create';
    public $save = 'manage/api/module/opx_menu/menu_edit/save';
    public $redirect = '/menu/edit/';

    /**
     * Make menu add form.
     *
     * @return  JsonResponse
     */
    public function getAdd(): JsonResponse
    {
        if(!AdminAuthorization::can('opx_menu::add')) {
            return $this->returnNotAuthorizedResponse();
        }

        $template = new Templater(OpxMenu::getTemplateFileName('menu.php'));

        $template->fillDefaults();

        return $this->responseFormComponent(0, $template, $this->addCaption, $this->create);
    }

    /**
     * Make menu edit form.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function getEdit(Request $request): JsonResponse
    {
        if(!AdminAuthorization::can('opx_menu::edit')) {
            return $this->returnNotAuthorizedResponse();
        }

        $id = $request->input('id');

        /** @var Menu $menu */
        $menu = Menu::withTrashed()->where('id', $id)->firstOrFail();

        $template = $this->makeTemplate($menu, 'menu.php');

        return $this->responseFormComponent($id, $template, $this->editCaption, $this->save);
    }

    /**
     * Create new menu.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function postCreate(Request $request): JsonResponse
    {
        if(!AdminAuthorization::can('opx_menu::add')) {
            return $this->returnNotAuthorizedResponse();
        }

        $template = new Templater(OpxMenu::getTemplateFileName('menu.php'));

        $template->resolvePermissions();

        $template->fillValuesFromRequest($request);

        if (!$template->validate()) {
            return $this->responseValidationError($template->getValidationErrors());
        }

        $values = $template->getEditableValues();

        $menu = $this->updateMenuData(new Menu(), $values);

        // Refill template
        $template = $this->makeTemplate($menu, 'menu.php');

        $id = $menu->getAttribute('id');

        return $this->responseFormComponent($id, $template, $this->editCaption, $this->save, $this->redirect . $id);
    }

    /**
     * Save menu.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function postSave(Request $request): JsonResponse
    {
        if(!AdminAuthorization::can('opx_menu::edit')) {
            return $this->returnNotAuthorizedResponse();
        }

        $id = $request->input('id');

        /** @var Menu $menu */
        $menu = Menu::withTrashed()->where('id', $id)->firstOrFail();

        $template = $template = $this->makeTemplate($menu, 'menu.php');

        $template->resolvePermissions();

        $template->fillValuesFromRequest($request);

        if (!$template->validate()) {
            return $this->responseValidationError($template->getValidationErrors());
        }

        $values = $template->getEditableValues();

        $menu = $this->updateMenuData($menu, $values);

        // Refill template
        $template = $this->makeTemplate($menu, 'menu.php');
        $id = $menu->getAttribute('id');

        return $this->responseFormComponent($id, $template, $this->editCaption, $this->save);
    }

    /**
     * Fill template with data.
     *
     * @param string $filename
     * @param Menu $menu
     *
     * @return  Templater
     */
    protected function makeTemplate(Menu $menu, $filename): Templater
    {
        $template = new Templater(OpxMenu::getTemplateFileName($filename));

        $template->fillValuesFromObject($menu);

        return $template;
    }

    /**
     * Store data to item.
     *
     * @param Menu $menu
     * @param $values
     *
     * @return  Menu
     */
    protected function updateMenuData(Menu $menu, $values): Menu
    {
        $this->setAttributes($menu, $values, ['name', 'alias', 'class']);

        $menu->save();

        return $menu;
    }
}