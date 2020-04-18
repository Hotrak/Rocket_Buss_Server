<?php

namespace App\Http\Controllers;

use App\Car;
use App\Driver;
use App\Order;
use App\ReserveCar;
use App\ReserveDriver;
use App\Route;
use App\Schedule;
use App\ScheduleRoute;
use App\TownConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public function __construct()
    {
//        $this->middleware('role:admin')->except(['store','orders']);
    }


    public function index(Request $request){

        $schedule = new Schedule();
        return $schedule->scheduleWithCountPlaces($request->town_connection_id,$request->date);
    }

    public function shortSchedule(Request $request){
        $schedule = new Schedule();
        return $schedule->scheduleShortWithCountPlaces($request->town_connection_id,$request->date);
    }


    public function store(Request $request){
        $this->autoStore('2020-04-11',1);
        $this->autoStore('2020-04-12',1);
    }
    public function autoStore($date,$conn_group_id){

        $dayOfWeek = date("N",strtotime($date));
        $reserveCount = 5;

        $drivers = Driver::all();
        $cars = Car::all();
//        $routes = Route::all()->filter(function ($item) use (&$conn_group_id) {
//                return $item->conn_group_id == $conn_group_id || $item->conn_group_id == $conn_group_id+1;
//            })
//            ->join('')
//            ->sortBy('route_group');

        $routes =  DB::table('routes')
            ->select('routes.time','routes.id','routes.route_group')
            ->join('route_days','routes.route_days_group','=','route_days.route_days_group')
            ->where('route_days.weekday','=', $dayOfWeek)
            ->whereBetween('routes.conn_group_id', [$conn_group_id, $conn_group_id+1])
            ->groupBy('routes.time','routes.id','routes.route_group')
            ->get();


        $counter = 0;
        $schedule = new Schedule();;
        $count = 0;
        $temp = null;
        foreach ($routes as $route){

            if (!isset($temp)||$temp->route_group !== $route->route_group){
                $schedule = new Schedule();
                $schedule->car_id = $cars[$counter]->id;
                $schedule->driver_id = $drivers[$counter]->id;
                $schedule->route_id = 1;
                $schedule->date_start = $date;
                $schedule->save();
                $counter = rand(0,4);
            }

            $scheduleRoute = new ScheduleRoute();
            $scheduleRoute->schedule_id = $schedule->id;
            $scheduleRoute->route_id = $route->id;
            $scheduleRoute->save();
            $count++;
            $temp = $route;
        }

        for($i = 0;$i<$reserveCount;$i++){
            $reserveCar = new ReserveCar;
            $reserveCar->reserve_date = $date;
            $reserveCar->car_id = rand(0,4);
            $reserveCar->save();

            $reserveDriver = new ReserveDriver;
            $reserveDriver->reserve_date = $date;
            $reserveDriver->driver_id = rand(0,4);
            $reserveDriver->save();
        }

        return 123;

    }

    public function addDriver($scheduleId){

    }

    public function addCar(Request $request){
        $scheduleRoute = ScheduleRoute::find($request->schedule_id);
        $schedule  = Schedule::find($scheduleRoute->schedule_id);

        $newSchedule = new Schedule();
        $newSchedule->car_id = Car::all()->random()->id;
        $newSchedule->driver_id = Driver::all()->random()->id;
        $newSchedule->date_start = $schedule->date_start;
        $newSchedule->save();

        $scheduleRoutes = ScheduleRoute::where('schedule_id','=',$schedule->id)->get();

        foreach($scheduleRoutes as $item){
            $newScheduleRoute = new ScheduleRoute();
            $newScheduleRoute->schedule_id = $newSchedule->id;
            $newScheduleRoute->route_id = $item->route_id;
            $newScheduleRoute->save();
        }

        $newSchedule->driver;
        $newSchedule->car;

        return $newSchedule;
    }

    public function orders($scheduleId){
        $order = new Order();
        return $order->ordersByScheduleId($scheduleId);
    }
}
