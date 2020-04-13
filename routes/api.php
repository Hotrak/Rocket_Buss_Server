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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

//Route::get('/routes', 'RouteController@index');
Route::get('/routes/{id}/{date}/{countPlaces}', 'RouteController@times');
//Route::get('/routes/points/{id}', 'RouteController@points');
Route::post('/order/create', 'RouteController@order');

Route::post('/login','UserController@login');
Route::post('/register','UserController@store');



Route::middleware('auth:api')->group(function () {
    Route::get('/user','UserController@index');
    Route::post('/logout', 'UserController@logout')->name('logout');
    Route::put('/users/update/{id}', 'UserController@update');

    Route::get('/drivers','DriverController@index');
    Route::post('/drivers','DriverController@store');
    Route::put('/drivers/{id}','DriverController@update');
    Route::delete('/drivers/{id}','DriverController@destroy');

    Route::get('/cars','CarController@index');
    Route::post('/cars','CarController@store');
    Route::put('/cars/{id}','CarController@update');
    Route::put('/cars/{id}/state','CarController@updateState');
    Route::delete('/cars/{id}','CarController@destroy');

    Route::get('/colors','ColorController@index');
    Route::post('/colors','ColorController@store');

    Route::get('/car_models','CarModelController@index');
    Route::post('/car_models','CarModelController@store');

    Route::get('/routes', 'TownConnectionController@index');
    Route::post('/routes', 'TownConnectionController@store');
    Route::get('/routes/points/{id}', 'TownController@points');

    Route::get('/towns', 'TownController@index');
    Route::post('/towns', 'TownController@store');

    Route::get('/orders/{userId}','OrderController@ordersByUserId');
    Route::post('/orders','OrderController@store');
    Route::put('/orders/{id}/status','OrderController@updateStatus');
    Route::delete('/orders/{id}','OrderController@destroy');

    Route::get('/reserves/cars','ReserveController@reservesCars');
    Route::get('/reserves/drivers','ReserveController@reservesDrivers');

    Route::get('/schedule','ScheduleController@index');
    Route::get('/schedule_short','ScheduleController@shortSchedule');
    Route::post('/schedule','ScheduleController@store');
    Route::get('/schedule/{id}/orders','ScheduleController@orders');
    Route::post('/schedule/car','ScheduleController@addCar');

});


Route::get('/test',function (){
    $users  = \App\User::all();
    return response()->json(["users"=>$users],200);
});

