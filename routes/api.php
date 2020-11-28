<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::prefix('v1')
    ->name('api.v1.')
    ->namespace('Api')
    ->group(function () {
        Route::middleware('throttle:' . config('api.rate_limits.sign'))->group(function () {
            //发送短信验证码
            Route::post('verificationCodes', 'VerificationCodesController@store')->name('verificationCodes.store');
            //用户注册
            Route::post('users', 'UsersController@store')->name('users.store');
            //获取图片验证码
            Route::post('captchas', 'CaptchasController@store')->name('captchas.store');
            //第三方登录
            Route::post('socials/{social_type}/authorizations', 'AuthorizationsController@socialStore')
                ->where('social_type', 'wechat')
                ->name('socials.authorizations.store');
            Route::post('authorizations', 'AuthorizationsController@store')
                ->name('authorizations.store');
            Route::put('authorizations/current', 'AuthorizationsController@update')
                ->name('authorizations.update');
            Route::delete('authorizations/current', 'AuthorizationsController@destroy')
                ->name('authorizations.destroy');
        });
        Route::middleware('throttle:' . config('api.rate_limits.access'))->group(function () {
            //游客访问的接口
            Route::get('users/{user}', 'UsersController@show')->name('users.show');
            //登陆用户访问接口
            Route::middleware('auth:api')->group(function () {
                //展示用户个人信息
                Route::get('user', 'UsersController@me')->name('user.show');
                //图片上传
                Route::post('images','ImagesController@store')->name('images.store');
                //用户个人资料更新
                Route::patch('user','UsersController@update')->name('user.update');
            });
        });
    });
