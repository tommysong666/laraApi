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
            //获取分类列表
            Route::get('categories','CategoriesController@index')->name('categories.index');
            //话题列表详情
            Route::resource('topics','TopicsController')->only('index','show');
            //话题回复列表
            Route::get('topics/{topic}/replies','RepliesController@index')->name('topics.replies.index');
            //某个用户的回复列表
            Route::get('users/{user}/replies','RepliesController@userIndex')->name('users.replies.index');
            //某个用户发布的话题
            Route::get('users/{user}/topics','TopicsController@userIndex')->name('user.topics.index');
            //登陆用户访问接口
            Route::middleware('auth:api')->group(function () {
                //展示用户个人信息
                Route::get('user', 'UsersController@me')->name('user.show');
                //图片上传
                Route::post('images','ImagesController@store')->name('images.store');
                //用户个人资料更新
                Route::patch('user','UsersController@update')->name('user.update');
                //发布，修改，删除话题
                Route::resource('topics','TopicsController')->only('store','update','destroy');
                //发表回复
                Route::post('topics/{topic}/replies','RepliesController@store')
                    ->name('topics.replies.store');
                //删除回复
                Route::delete('topics/{topic}/replies/{reply}','RepliesController@destroy')
                    ->name('topics.replies.destroy');
                Route::get('notifications','NotificationsController@index')
                    ->name('notifications.index');
            });
        });
    });
