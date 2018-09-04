<?php

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'Auth\AuthApiController@login');
    Route::post('signup', 'Auth\AuthApiController@signup');
    Route::get('signup/activate/{token}','Auth\AuthApiController@signUpActivate')

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('logout', 'Auth\AuthApiController@logout');
        Route::get('user', 'Auth\AuthApiController@user');
    });
});
