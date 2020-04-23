<?php

namespace App\Http\Controllers;

use App\Car;
use App\Driver;
use App\Order;
use App\ReserveCar;
use App\ReserveDriver;
use App\Route;
use App\Schedule;
use App\ScheduleCreateInfo;
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

    public function singleRoute(Request $request){
        $schedule = new Schedule();
        return $schedule->singleRouteWithCountPlaces($request->town_connection_id,$request->date,$request->route_id);
    }


    public function store(Request $request){
        $this->autoStore('2020-04-25',1);
//        $this->autoStore('2020-04-25',1);
//        $this->autoStore('2020-04-22',1);
    }

    public function autoStore($date,$conn_group_id){

        $dayOfWeek = date("N",strtotime($date));

        $yesterday = date('Y-m-d',strtotime($date." - 1 day"));
        $yesterdayDayOfWeek = date("N",strtotime($yesterday));

        $scheduleCreateInfo = ScheduleCreateInfo::whereDate('schedule_date','=',$date)->first();
        $counterCars = 0;
        $counterDrivers = 0;

        $unWorkedDrivers = [];

        $isTodayScheduleCreateInfo = false;

        $currentScheduleCreateInfo = null;
        if(isset($scheduleCreateInfo)){
            $currentScheduleCreateInfo = $scheduleCreateInfo;
            $isTodayScheduleCreateInfo = true;

        } else {
            $scheduleCreateInfo = ScheduleCreateInfo::whereDate('schedule_date','=',$yesterday)->first();
            if(isset($scheduleCreateInfo)){
                $currentScheduleCreateInfo = $scheduleCreateInfo;
            }
        }
        if(isset($currentScheduleCreateInfo)){
            $workedDrivers = unserialize($currentScheduleCreateInfo->schedule_drivers);
            $unWorkedDrivers = unserialize($currentScheduleCreateInfo->schedule_holidays_drivers);

            $countWorkedDrivers = count($workedDrivers);
            if($countWorkedDrivers!= 0)
                $counterDrivers = $workedDrivers[$countWorkedDrivers-1]+1;

            $counterCars = 0;
        }


        $reserveCount = 5;

        $drivers = Driver::all();
        $cars = Car::all();


        $routes =  DB::table('routes')
            ->select('routes.time','routes.id','routes.route_group','routes.route_days_group')
            ->join('route_days','routes.route_days_group','=','route_days.route_days_group')
            ->where('route_days.weekday','=', $dayOfWeek)
            ->whereBetween('routes.conn_group_id', [$conn_group_id, $conn_group_id+1])
            ->orderBy('routes.route_group')
            ->groupBy('routes.time','routes.id','routes.route_group','routes.route_days_group')
            ->get();
//        dd($routes);

        $workedDrivers = [];
        $newUnWorkedDrivers =[];
        if($isTodayScheduleCreateInfo)
            $newUnWorkedDrivers =$unWorkedDrivers;



        $currentDriver = null;
        $unWorkerCount = 0;
        $count = 0;
        $temp = null;
        foreach ($routes as $route){

            if (!isset($temp)||$temp->route_group !== $route->route_group){

                $isFind = false;
                while (!$isFind){

                    if($unWorkerCount < count($unWorkedDrivers) && !$isTodayScheduleCreateInfo){
                        $currentDriver =  $unWorkedDrivers[$unWorkerCount];
                        $newUnWorkedDrivers[] =  $currentDriver;
                        $unWorkerCount++;
                        $isFind = true;
                    }
                    else if($counterDrivers < count($drivers)){

                        if($drivers[$counterDrivers]->week_end != $dayOfWeek ){
                            $workedDrivers[] = $drivers[$counterDrivers]->id;
                            $currentDriver = $drivers[$counterDrivers]->id;
                            $isFind = true;
                        }else{
                            $newUnWorkedDrivers[] = $drivers[$counterDrivers]->id;
                            $counterDrivers++;
                        }
                    }
                    else
                        $counterDrivers = 0;
                }


                $schedule = new Schedule();
                $schedule->car_id = $cars[$counterCars]->id;
                $schedule->driver_id = $currentDriver;
                $schedule->route_id = 1;
                $schedule->date_start = $date;
                $schedule->save();


                if($counterCars+1 < count($cars)) $counterCars++;
                else $counterCars = 0;

                $counterDrivers++;

            }

            $newScheduleRoute = new ScheduleRoute();
            $newScheduleRoute->schedule_id = $schedule->id;
            $newScheduleRoute->route_id = $route->id;
            $newScheduleRoute->save();


            $temp = $route;
            $count++;
        }
//        $array = [];
//        dd(serialize($workedDrivers),unserialize(serialize($array)));
//        dd($workedDrivers,$unWorkedDrivers,serialize($workedDrivers),unserialize(serialize($workedDrivers)));

        if($isTodayScheduleCreateInfo)
            $scheduleCreateInfo = $currentScheduleCreateInfo;
        else
            $scheduleCreateInfo = new ScheduleCreateInfo;

        $scheduleCreateInfo->schedule_date = $date;
        $scheduleCreateInfo->schedule_drivers = serialize($workedDrivers);
        $scheduleCreateInfo->schedule_holidays_drivers = serialize(array_unique($newUnWorkedDrivers));
        $scheduleCreateInfo->save();




        return 123;

    }

    private function createReservation($count, $date){


        for($i = 0;$i<$count;$i++){
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

    public function getDriversInterval($date){
        $result = DB::table('schedules')
            ->where('date_start','=',$date)
            ->select(
                DB::raw('MIN(schedules.driver_id) as min_id'),
                DB::raw('MAX(schedules.driver_id) as max_id')
            )
            ->first();

        if(!isset($result->max_id))
            return null;
        return $result;
    }

    public function getDriversByInterval($interval,$weekEnd){
        $result = DB::table('drivers')
            ->whereBetween('drivers.id',$interval->min_id,$interval->max_id)
            ->where('drivers.week_end','=',$weekEnd)
            ->select(
               "drivers.id"
            )
            ->get();
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
