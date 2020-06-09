<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\ReserveCar;
use App\Role;
use App\Schedule;
use App\Town;
use App\TownConnection;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;

//$data = date("N",strtotime('2020-03-30'));
//Route::get('/{time}', 'TownConnectionController@roundTime');
Route::get('/22222', function (){
    $price = DB::table('town_connections')
        ->select('town_connections.price')
        ->join('routes','routes.town_connection_id','=','town_connections.id')
        ->join('schedule_routes','schedule_routes.route_id','=','routes.id')
        ->where('schedule_routes.id','=',882)//schedule_route_id
        ->first()->price;
    dd($price);
});
//Route::get('/', 'ScheduleController@store');
Route::get('/debug', function (){
//    $schedule = new Schedule();
//    $scheduleByScheduleRouteId = $schedule->singleRouteByScheduleRouteId(353);
//    dd($scheduleByScheduleRouteId);

    $reserveDrivers = new \App\ReserveDriver();
    $reserveDriver = $reserveDrivers->getRandomByDate('2020-05-30');
    dd($reserveDriver);

    //    $scheduleByRouteId = $schedule->singleRoute();
});
Route::get('/33', function (){

    $user =  User::all()->first();
//    $user->notify(new \App\Notifications\TestNotificaton($user));
//

//
//    dd($admins);
    //
    $towns = TownConnection::all()->fresh(['town1','town2']);
    dd($towns);
//    $test = \App\TownConnection::all()->max('group');
//    dd($test);
    return 'default';
});
//Route::get('/test', 'TestController@index');
Route::get('/test', 'ScheduleController@index');



//Route::group([
//    'middleware' => ['api', 'rest_my'],
//    'namespace' => $this->namespace,
//    'prefix' => 'api',
//], function ($router) {
//    //Add you routes here, for example:
//    Route::apiResource('/test','TestController');
//});
