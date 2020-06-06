<?php

namespace App\Http\Controllers;

use App\Car;
use App\CarManager;
use App\Driver;
use App\DriverManager;
use App\Order;
use App\ReserveCar;
use App\ReserveDriver;
use App\Route;
use App\Schedule;
use App\ScheduleCreateInfo;
use App\ScheduleRoute;
use App\TownConnection;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Element;
use function React\Promise\map;

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

//        dd($this->changeDriver(139));
//        dd(123);
        $this->storeReserve(3, '2020-06-06');

        $townRouteGroup = TownConnection::select('town_route_group')->groupBy('town_route_group')->get()->map(function ($item){
            return $item->town_route_group;
        });

        foreach ($townRouteGroup as $item){
            $isStore = $this->autoStore('2020-06-06',$item);
            if(!$isStore)
                dd("STORE_WITH_ERROR");

//            return;
        }
        return;
    }

    public function autoStore($date,$townRouteGroup){

        $dayOfWeek = date("N",strtotime($date));

        $cars = Car::all();

        $routes =  DB::table('routes')
            ->select('routes.time',
                'routes.id',
                'routes.route_group',
                'routes.route_days_group',
                'town_connections.time_drive',
                'town_connections.conn_group')
            ->join('route_days','routes.route_days_group','=','route_days.route_days_group')
            ->join('town_connections','town_connections.id','=','routes.town_connection_id')
            ->where('route_days.weekday','=', $dayOfWeek)
//            ->whereBetween('routes.conn_group_id', [$conn_group_id, $conn_group_id+1])
            ->where('town_connections.town_route_group','=',$townRouteGroup)
            ->orderBy('routes.route_group')
            ->orderBy('routes.time')
            ->orderByDesc('town_connections.time_drive')
            ->groupBy('routes.time','routes.id','routes.route_group','routes.route_days_group','town_connections.time_drive')
            ->get();
//        dd($routes);

        $currentDriver = null;
        $count = 0;
        $temp = null;

        $driverManager = new DriverManager($date);
        $carManager = new CarManager($date);


        foreach ($routes as $route){
            if (!isset($temp)||$temp->route_group !== $route->route_group){

                $currentDriver = $driverManager->getDriver($route->time_drive,$route->time);
                if($currentDriver == -1){
                    return false;
                }
                $currentCar = $carManager->getCar($route->time_drive,$route->time);
                if($currentCar == -1){
                    return false;
                }

                $schedule = new Schedule();
                $schedule->car_id = $currentCar;
                $schedule->driver_id = $currentDriver;
                $schedule->route_id = 1;
                $schedule->date_start = $date;
                $schedule->save();

            }

            $newScheduleRoute = new ScheduleRoute();
            $newScheduleRoute->schedule_id = $schedule->id;
            $newScheduleRoute->route_id = $route->id;
            $newScheduleRoute->save();

            $temp = $route;
            $count++;
        }
        $driverManager->saveChoseInfo();

        return true;
    }

    private function storeReserve($count, $date){

        $dayOfWeek = date("N",strtotime($date));

        $drivers = Driver::all();
        $driversCount = count($drivers);
        $driversCounter = 0;
        $currentDriver = null;

        $oldReservation = ReserveDriver::whereDate('reserve_date','=',$date)->get();

        if(count($oldReservation) != 0)
            return false;

        $oldReservation = ReserveDriver::select('driver_id')->orderByDesc('id')->first();
//        dd($oldReservation);

        if(isset($oldReservation)){
            $countD = 0;
            foreach ($drivers as $driver){
                if($driver->id == $oldReservation->driver_id){
                    $driversCounter = $countD+1;
                    break;
                }
                $countD++;
            }
        }
        $carManager = new CarManager($date);
        for($i = 0;$i<$count;$i++){

            $isFind = false;
            while (!$isFind){
                if($driversCounter >= $driversCount){
                    $driversCounter = 0;
                }
                if($drivers[$driversCounter]->week_end != $dayOfWeek){
                    $isFind = true;
                    $currentDriver = $drivers[$driversCounter];

                }
                $driversCounter++;

            }
            $reserveDriver = new ReserveDriver;
            $reserveDriver->reserve_date = $date;
            $reserveDriver->driver_id = $currentDriver->id;
            $reserveDriver->save();

            $currentCar = $carManager->getCar(250,'06:30:00');

            $reserveCar = new ReserveCar();
            $reserveCar->reserve_date = $date;
            $reserveCar->car_id = $currentCar;
            $reserveCar->save();

        }


        return true;
    }

    public function addDriver($scheduleId){

    }

    public function addCar(Request $request){

        $scheduleInfo = ScheduleRoute::where('schedule_routes.id','=',$request->schedule_id)
            ->join('schedules','schedules.id','=','schedule_routes.schedule_id')
            ->join('routes','routes.id','=','schedule_routes.route_id')
            ->join('town_connections','town_connections.id','=','routes.town_connection_id')
            ->select('schedules.id','schedules.date_start','routes.time','town_connections.time_drive','schedule_routes.route_id')
            ->first();

//        return $scheduleInfo;
        $carManager = new CarManager($scheduleInfo->date_start);
        $currentCar = $carManager->getCar($scheduleInfo->time_drive,$scheduleInfo->time);

        if($currentCar == -1){
            $reserveCar = new ReserveCar();
            $currentCar = $reserveCar->getRandomByDate($scheduleInfo->date_start);

            if(!isset($currentCar))
                return response(["message" => 'Недостаточно маршруток.'],422);
            else
                $currentCar = $currentCar->car_id;
        }
        $driverManager = new DriverManager($scheduleInfo->date_start);
        $currentDriver = $driverManager->getDriver($scheduleInfo->time_drive,$scheduleInfo->time);

        if($currentDriver == -1){
            $reserveDriver = new ReserveDriver();
            $currentDriver = $reserveDriver->getRandomByDate($scheduleInfo->date_start);

            if(!isset($currentDriver))
                return response(["message" => 'Недостаточно водителей.'],422);
            else
                $currentDriver = $currentDriver->driver_id;
        }

        $newSchedule = new Schedule();
        $newSchedule->car_id = $currentCar;
        $newSchedule->driver_id = $currentDriver;
        $newSchedule->date_start = $scheduleInfo->date_start;
        $newSchedule->save();

        $scheduleRoutes = ScheduleRoute::where('schedule_id','=',$scheduleInfo->id)->get();

        $currentNewScheduleRoute = null;

        foreach($scheduleRoutes as $item){
            $newScheduleRoute = new ScheduleRoute();
            $newScheduleRoute->schedule_id = $newSchedule->id;
            $newScheduleRoute->route_id = $item->route_id;
            $newScheduleRoute->save();

            if($scheduleInfo->route_id == $item->route_id)
                $currentNewScheduleRoute = $newScheduleRoute;
        }

        $newSchedule = $newSchedule->singleRouteByScheduleRouteId($currentNewScheduleRoute->id);
//        $newSchedule['count_places'] = 0;
        return response()->json(['item'=>$newSchedule]);
    }

    public function orders($scheduleId){
        $order = new Order();
        return $order->ordersByScheduleId($scheduleId);
    }

    public function changeDriver($scheduleId){
        $scheduleRoute = ScheduleRoute::find($scheduleId);
        $schedule = Schedule::find($scheduleRoute->schedule_id);

        $reserveDriver = new ReserveDriver();
        $reserveDriver = $reserveDriver->getRandomByDate($schedule->date_start);

        if(!isset($reserveDriver)){
            return response(["message" => 'Недостаточно водителей.'],422);
        }

        $schedule->driver_id = $reserveDriver->driver_id;
        $schedule->save();
        $schedule->driver;

        $newSchedule = $schedule->singleRouteByScheduleRouteId($scheduleRoute->id);

        return response(['schedule'=>$newSchedule]);

    }

    public function changeCar($scheduleId){
        $scheduleRoute = ScheduleRoute::find($scheduleId);
        $schedule = Schedule::find($scheduleRoute->schedule_id);

        $reserveCar = new ReserveCar();
        $reserveCar = $reserveCar->getRandomByDate($schedule->date_start);
        if(!isset($reserveCar)){
            return response(["message" => 'Недостаточно маршруток.'],422);
        }

        $schedule->car_id = $reserveCar->car_id;
        $schedule->save();
        $schedule->car;

        $newSchedule = $schedule->singleRouteByScheduleRouteId($scheduleRoute->id);

        return response(['schedule'=>$newSchedule]);

    }

    public function destroy($scheduleRouteId)
    {
        $scheduleRoute = ScheduleRoute::find($scheduleRouteId);
        $schedule = Schedule::find($scheduleRoute->schedule_id);
        $scheduleRoutes = ScheduleRoute::where('schedule_id','=',$schedule->id)->delete();

        $reserveDriver = ReserveDriver::whereDate('reserve_date','=',$schedule->date_start)
            ->where('reserve_drivers.driver_id','=',$schedule->driver_id)
            ->first();

        $reserveCar = ReserveCar::whereDate('reserve_date','=',$schedule->date_start)
            ->where('reserve_cars.car_id','=',$schedule->car_id)
            ->first();

        if(isset($reserveDriver)){
            $reserveDriver->reserve_state = 0;
            $reserveDriver->save();
        }

        if(isset($reserveCar)){
            $reserveCar->reserve_state = 0;
            $reserveCar->save();
        }

//        $scheduleRoute->delete();
        $schedule->delete();

        return response('OK',200);

    }
}
