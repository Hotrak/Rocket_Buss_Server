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
Route::post('/driver/login','UserController@driverLogin');
Route::post('/auth_telegram','UserController@authTelegram');
Route::post('/register','UserController@store');
Route::get('/routes/points/{id}', 'TownController@points');
Route::get('/schedule_short','ScheduleController@shortSchedule');

Route::get('/routes', 'TownConnectionController@index');


Route::middleware('auth:api')->group(function () {

    Route::get('/user','UserController@index');
    Route::put('/user/{id}/state','UserController@updateState');

    Route::get('/clients','UserController@clients');

    Route::get('/driver','UserController@driver');
    Route::post('/logout', 'UserController@logout')->name('logout');
    Route::put('/users/update/{id}', 'UserController@update');

    Route::get('/drivers','DriverController@index');
    Route::post('/drivers','DriverController@store');
    Route::put('/drivers/{id}','DriverController@update');
    Route::delete('/drivers/{id}','DriverController@destroy');
    Route::get('/drivers/{id}/schedule','DriverController@schedule');

    Route::get('/cars','CarController@index');
    Route::post('/cars','CarController@store');
    Route::put('/cars/{id}','CarController@update');
    Route::put('/cars/{id}/state','CarController@updateState');
    Route::delete('/cars/{id}','CarController@destroy');

    Route::get('/colors','ColorController@index');
    Route::post('/colors','ColorController@store');

    Route::get('/car_models','CarModelController@index');
    Route::post('/car_models','CarModelController@store');

    Route::post('/routes', 'TownConnectionController@store');

    Route::get('/towns', 'TownController@index');
    Route::post('/towns', 'TownController@store');
    Route::put('/towns/{id}', 'TownController@update');
    Route::get('/towns/{id}/points', 'TownController@allPoints');

    Route::get('/orders/{userId}','OrderController@ordersByUserId');

    Route::put('/orders/{id}','OrderController@update');

    Route::put('/orders/{id}/status','OrderController@updateStatus');
    Route::get('/orders/driver/{id}/{schedule_route_id}','OrderController@ordersByTownConnGroup');
    Route::delete('/orders/{id}','OrderController@destroy');
    Route::get('/telegram/{id}/orders','OrderController@telegramUserOrders');

    Route::get('/reserves/cars','ReserveController@reservesCars');
    Route::delete('/reserves/{id}/cars','ReserveController@destroyCar');
    Route::get('/reserves/drivers','ReserveController@reservesDrivers');
    Route::delete('/reserves/{id}/drivers/','ReserveController@destroyDriver');

    Route::get('/schedule','ScheduleController@index');
    Route::post('/schedule','ScheduleController@store');
    Route::put('/schedule/{id}/driver','ScheduleController@changeDriver');
    Route::put('/schedule/{id}/car','ScheduleController@changeCar');
    Route::delete('/schedule/{id}','ScheduleController@destroy');

    Route::get('/schedule/{id}/orders','ScheduleController@orders');
    Route::post('/schedule/car','ScheduleController@addCar');


    Route::get('/settings','SettingsController@index');
    Route::put('/settings','SettingsController@update');

    Route::get('/users/{user_id}/notifications','NotificationController@index');
    Route::put('/users/{user_id}/notifications/{n_id}','NotificationController@markAsRead');
    Route::get('/orders/show/{id}','OrderController@show');

});

    Route::get('/statistics','StatisticsController@statistics');
    Route::get('/statistics/menu','StatisticsController@statisticMenu');

Route::post('/orders','OrderController@store');// Не тута

Route::resource('news', 'NewsController');
Route::resource('lost_things', 'LostThingsController');
Route::get('/single_route','ScheduleController@singleRoute');


Route::get('/test',function (){
    $users  = \App\User::all();
    return response()->json(["users"=>$users],200);
});

