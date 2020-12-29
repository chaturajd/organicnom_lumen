<?php

use Illuminate\Support\Facades\Route;

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

Route::get('/signin','LoginController@signin');
Route::get('/signout','LoginController@signout');
Route::get('/refreshToken','LoginController@reissueToken');


Route::group(['prefix' => 'organicnom', 'middleware'=>'auth'] ,function () {
    Route::get('/exercises/all','OrganicnomController@getAllExercises');
    Route::get('/lessons/all','OrganicnomController@getAllLessons');
    Route::get('/pointers/get','OrganicnomController@getPointers');
    Route::post('/pointers/store','OrganicnomController@storePointers');

});