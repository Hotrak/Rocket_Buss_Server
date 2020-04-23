<?php

namespace App\Http\Controllers;

use App\Driver;
use App\Http\Requests\DriverRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DriverController extends Controller
{
    public function __construct()
    {
//        $this->middleware('role:admin');
    }

    public function index(){
        $drivers = Driver::all()->fresh('user');
        return $drivers;
    }
    public function store(DriverRequest $request){

        $validated = $request->validated();
        $request['password']=Hash::make($request->password);
        $user = User::create($request->all(['name','email','phone','password']));

        $driver = Driver::create($request->all(['name','surname','patronymic','birthday','med_exam','town','street','home_num','week_end']));
        $driver->user_id = $user->id;
        $driver->save();
        $driver->user;
        return $driver;
    }

    public function update(DriverRequest $request,$id){
        $validated = $request->validated();

        $driver = Driver::find($id);
        $driver->name = $request->name;
        $driver->surname = $request->surname;
        $driver->patronymic = $request->patronymic;
        $driver->med_exam = $request->med_exam;
        $driver->birthday = $request->birthday;
        $driver->town = $request->town;
        $driver->street = $request->street;
        $driver->home_num = $request->home_num;
        $driver->week_end = $request->week_end;
        $driver->save();
        $driver->user;

        return $driver;
    }

    public function schedule(){
        $user = auth()->user();
        $driverId = 10;
//        $user->driver->id

        $driverOrders = 'SELECT sch.id,
        (SELECT  t.name FROM towns t WHERE t.id= tc.town1_id) AS town1_name,
        (SELECT t2.name FROM towns t2 WHERE t2.id= tc.town2_id) AS town2_name,
        sch.date_start,TIME_FORMAT(ro.time , "%H:%i") as time,tc.conn_group
        FROM schedule_routes sr
              JOIN schedules sch ON sch.id = sr.schedule_id
              LEFT JOIN routes ro ON ro.id = sr.route_id
              LEFT JOIN town_connections tc ON tc.id = ro.town_connection_id
              where sch.driver_id=:driverId and tc.index_pos = 0 
              order by sch.date_start';

        $orders = DB::select($driverOrders,['driverId'=>$driverId]);
        return $orders;

    }

    public function destroy($id){
        Driver::find($id)->delete();
    }
}
