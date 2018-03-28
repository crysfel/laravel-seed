<?php

use Illuminate\Http\Request;

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

Route::group(['middleware' => 'cors'], function () {
    
    Route::group(['prefix' => 'v1'], function () {
        Route::post('auth/signup', 'Api\AuthController@signup');
        Route::post('auth/login', 'Api\AuthController@authenticate');

        Route::group(['middleware' => 'jwt.auth'], function() {
            Route::get('users', 'Api\UserController@index');
        });
    });
});
