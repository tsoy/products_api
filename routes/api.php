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


Route::middleware(['auth:api', 'accept_json'])->group(function () {

    Route::get('products', 'ProductController@index');

    Route::post('products', 'ProductController@create');
    Route::get('products/{product}', 'ProductController@get');
    Route::post('products/{product}', 'ProductController@update');
    Route::delete('products/{product}', 'ProductController@delete');

    Route::post('products/{product}/image', 'ProductController@uploadImage');

    Route::post('user/products', 'ProductController@attachProduct');
    Route::delete('user/products', 'ProductController@detachProduct');
    Route::get('user/products', 'ProductController@userProducts');

});