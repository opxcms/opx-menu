<?php

namespace Modules\Opx\Menu;

use Core\Foundation\Module\RouteRegistrar as BaseRouteRegistrar;
use Illuminate\Support\Facades\Route;

class RouteRegistrar extends BaseRouteRegistrar
{
    public function registerPublicRoutes($profile): void
    {
        // Login routes

//        Route::get('login', 'Modules\Opx\Users\Controllers\AuthController@showAuthForm')
//            ->name('opx_user_login')
//            ->middleware(['web', 'guest:user']);
//
//        Route::get('logout', 'Modules\Opx\Users\Controllers\AuthController@logout')
//            ->name('opx_user_logout')
//            ->middleware(['web', 'auth:user']);

        // Password reset routes

//        Route::get('login/reset', 'Modules\Opx\User\Controllers\ResetPasswordController@showResetForm')
//            ->name('opx_user_reset_password')
//            ->middleware(['web', 'guest:user']);

        // Email confirmation routes

//        Route::get('email/confirm', 'Modules\Opx\User\Controllers\EmailConfirmController@showLinkRequestForm')
//            ->name('opx_user_confirm_email')
//            ->middleware('web');
//
//        Route::post('email/confirm', 'Modules\Opx\User\Controllers\EmailConfirmController@sendConfirmationLinkEmail')
//            ->name('opx_user_confirm_email')
//            ->middleware('web');
//
//        Route::get('email/confirm/{token}', 'Modules\Opx\User\Controllers\EmailConfirmController@confirm')
//            ->name('opx_user_confirm_email_check')
//            ->middleware('web');
    }

    public function registerPublicAPIRoutes($profile): void
    {
        // Registration
//        Route::post('api/user/register', 'Modules\Opx\User\Controllers\RegisterController@register')
//            ->name('opx_user_register')
//            ->middleware(['web', 'guest:user']);

        // Login
//        Route::post('api/user/login', 'Modules\Opx\User\Controllers\LoginController@login')
//            ->name('opx_user_login')
//            ->middleware(['web', 'guest:user']);

        // Logout
//        Route::post('api/user/logout', 'Modules\Opx\User\Controllers\LoginController@logout')
//            ->name('opx_user_logout')
//            ->middleware(['web', 'auth:user']);

        // Forget password
//        Route::post('api/user/forgot', 'Modules\Opx\User\Controllers\ForgotPasswordController@sendToken')
//            ->name('opx_user_forgot')
//            ->middleware('web', 'guest:user');

        // Reset password
//        Route::post('api/user/reset', 'Modules\Opx\User\Controllers\ResetPasswordController@reset')
//            ->name('opx_user_reset')
//            ->middleware('web', 'guest:user');

    }

}