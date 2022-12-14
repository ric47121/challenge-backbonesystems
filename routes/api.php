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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace'=> 'Api'], function(){

    Route::get('zip-codes-redis/{zip_code}', 'ZipCodeController@index_redis');
    Route::get('zip-codes-indice-file/{zip_code}', 'ZipCodeController@index_indice_file');
    Route::get('zip-codes/{zip_code}', 'ZipCodeController@index');
    
});