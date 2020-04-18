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
use Illuminate\Support\Facades\Route;

//$data = date("N",strtotime('2020-03-30'));
//Route::get('/{time}', 'TownConnectionController@roundTime');
Route::get('/', function (){
//    $today = now();
    $today_dt = new DateTime();
    $expire_dt = new DateTime("2020-04-17");
        $time1 = strtotime($today_dt->format("H:i"));
        $time2 = strtotime("12:30:00");

//        if($time1 < $time2)
//            dd(1);
//        else
//            dd(0);

        if($today_dt->format("Y-m-d") == $expire_dt->format("Y-m-d")) {
            dd(1);
        }
            dd(0);

//    if ($expire_dt < $today_dt)
});
//Route::get('/', 'ScheduleController@store');
Route::get('/33', function (){

//    $user =  User::find(19);
////    $user->notify(new \App\Notifications\TestNotificaton($user));
//
//    $admins = User::all()->filter(function ($user){
//        return $user->hasRole('admin');
//    });
//
//    Notification::send($admins,new \App\Notifications\TestNotificaton($user));
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
