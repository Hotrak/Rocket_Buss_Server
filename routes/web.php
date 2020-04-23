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
   $con =  new \App\Http\Controllers\DriverController();
   $schedule = $con->schedule();
    dd($schedule);
});
//Route::get('/', 'ScheduleController@store');
Route::get('/33', function (){

    $user =  User::all()->first();
//    $user->notify(new \App\Notifications\TestNotificaton($user));
//
    $admins = User::all()->filter(function ($user){
        return $user->hasRole('admin');
    });

    Notification::send($admins,new \App\Notifications\TestNotificaton($user));
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
