<?php

namespace App\Http\Controllers;

use App\Route;
use App\RouteDay;
use App\RouteTowns;
use App\Town;
use App\TownConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TownConnectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin')->only('store');
    }

    public function index(){
//        $towns = TownConnection::all()->fresh(['town1','town2']);
        $towns = TownConnection::orderBy('town_connections.town1_id')
            ->orderBy('town_connections.town2_id')
            ->with(['town1','town2'])
            ->get();

//        $count = 0;
//        $result = [];
//        foreach ($towns as $item){
//
//            if($count == 0){
//                $result[] = $item;
//            }
//            else{
//
//                if($item->price != $result[$count]->price &&
//                    $item->town1_id != $result[$count]->town1_id  &&
//                    $item->town2_id != $result[$count]->town2_id){
//
//                    $result[] = $item;
//                    $count++;
//                }
//            }
//
//        }

        return $towns;
    }
    public function store(Request $request){
        $townConnectionGroup = \App\TownConnection::all()->max('conn_group');
        if(!isset($townConnectionGroup))
            $townConnectionGroup = 0;

        $townConnectionGroup++;

        $townRouteGroup = \App\TownConnection::all()->max('town_route_group');
        if(!isset($townRouteGroup))
            $townRouteGroup = 0;

        $townRouteGroup++;


        $routeDaysGroup = \App\RouteDay::all()->max('route_days_group');
        if(!isset($routeDaysGroup))
            $routeDaysGroup = 0;
        $routeDaysGroup++;
        $routeDayGroups = [];
        foreach ($request->rhythm as $rhythmItem){
            foreach ($rhythmItem['weekdays'] as  $item){
                $routeDay =  new \App\RouteDay;
                $routeDay->route_days_group = $routeDaysGroup;
                $routeDay->weekday = $item;
                $routeDay->save();
            }
            $routeDayGroups[] = $routeDaysGroup;
            $routeDaysGroup++;
        }

        $routesCount = count($request->routes);
        $temp = 0;
        $fixedTimeDrive = 0;
        $maxTimeDrive=0;
        $routeCounter = 0;
        for($i=0;$i<$routesCount ;$i++){
            $routeStart = $request->routes[$i];
            $timeDrive = 0;
            $price = 0;

            for($j=$i+1; $j< $routesCount ;$j++){
                $routeCounter++;
                $routeEnd = $request->routes[$j];
                $townConnection = new TownConnection();
                $townConnection->town1_id = $routeStart['townId'];
                $townConnection->town2_id = $routeEnd['townId'];
                $townConnection->conn_group = $townConnectionGroup;
                $townConnection->town_route_group = $townRouteGroup;
                $townConnection->is_back = 0;
                $townConnection->town_x = $i+1;
                $townConnection->town_y = $j+1;
                if($routesCount>2)
                    $townConnection->is_transit = 1;
                else
                    $townConnection->is_transit = 0;

                $timeDrive+= $routeEnd['time'];
                $townConnection->time_drive = $timeDrive;

                if($timeDrive>$maxTimeDrive)
                    $maxTimeDrive = $timeDrive;

                $townConnection->price = $price + $routeEnd['price'];
                $indexPoss = $routeCounter-$temp;

                if($i!==0){
                    $fixedTimeDrive += $routeStart['time'];
                }


                $price+=$routeEnd['price'];


                if($j+1 == $routesCount && $i==0){
                    $townConnection->index_pos = 0;
                    $indexPoss = 0;
                    $temp=1;
                }

                $townConnection->index_pos = $indexPoss;
                $townConnection->save();


                $count = 0;
                $rhythmCount = 0;

                foreach ($request->rhythm as $rhythmItem){

                    foreach ($rhythmItem['times'] as $item){
                        $route = new Route();
                        $route->town_connection_id = $townConnection->id;
                        $route->route_group = $count;
                        $route->route_days_group = $routeDayGroups[$rhythmCount];
                        $route->conn_group_id = $townConnection->conn_group;
                        $route->time_order_id = 1;
                        $route->time =  date("H:i:s",strtotime($item['time'].':00') + $fixedTimeDrive*60);
                        $route->save();
                        $count++;
                    }
                    $rhythmCount++;

                }


            }
        }
        $this->storeReverse($request->routes,$request->rhythm,$maxTimeDrive,$townConnectionGroup,$routeDayGroups,$townRouteGroup);
        return TownConnection::all()->fresh(['town1','town2']);
    }

    private function storeReverse($routes,$rhythm,$maxTimeDrive,$townConnectionGroup,$routeDayGroups,$townRouteGroup){
        $routes = array_reverse($routes);
        $routesCount = count($routes);
        $temp = 0;
        $fixedTimeDrive = 0;
        $routeCounter = 0;
        $tempRouteEnd = '';

        for($i=0;$i<$routesCount ;$i++){
            $routeStart = $routes[$i];
            $timeDrive = 0;
            $price = 0;

            for($j=$i+1; $j< $routesCount ;$j++){
                $routeCounter++;
                $routeEnd = $routes[$j];
                $townConnection = new TownConnection;
                $townConnection->town1_id = $routeStart['townId'];
                $townConnection->town2_id = $routeEnd['townId'];
                $townConnection->conn_group = $townConnectionGroup+1;
                $townConnection->town_route_group = $townRouteGroup;
                $townConnection->is_back = 1;
                $townConnection->town_x = $i+1;
                $townConnection->town_y = $j+1;
                if($routesCount>2)
                    $townConnection->is_transit = 1;
                else
                    $townConnection->is_transit = 0;

                $timeDrive+= $routes[$j-1]['time'];//+
                $townConnection->time_drive = $timeDrive;


                $price+=$routes[$j-1]['price'];//+
                $townConnection->price = $price;//+$routeStart
                $indexPoss = $routeCounter-$temp;

                if($i!==0){
                    $fixedTimeDrive += $routes[$j-2]['time'];//$routeStart
                }

                $townConnection->index_pos = 0;


                $townConnection->save();

                $count = 0;
                $rhythmCount = 0;
                foreach ($rhythm as $rhythmItem){
                    foreach ($rhythmItem['times'] as $item){
                        $route = new Route();
                        $route->town_connection_id = $townConnection->id;
                        $route->route_group = $count;
                        $route->route_days_group = $routeDayGroups[$rhythmCount];
                        $route->conn_group_id = $townConnection->conn_group;
                        $route->time_order_id = 1;
                        $route->time = $this->roundTime(date("H:i:s",strtotime($item['time'].':00') + (30+$maxTimeDrive +$fixedTimeDrive)*60));
                        $route->save();
                        $count++;
                    }
                    $rhythmCount++;

                }

            }
        }
    }

    public function roundTime($time){

        list($hours,$minutes,$sec) =  explode(":", $time);

        if($minutes == '00')
            return $time;
        for($i = 0;$i< 60;$i++){
            if($minutes == 30){
                return $hours.':'.$minutes.':00';
            }
            if($minutes == 59){
                $hours++;
                return  $hours.':00:00';
            }
            $minutes++;

        }
    }

    public function show($connGroupId){
        $townConnections = TownConnection::where('conn_group',$connGroupId)
            ->where(DB::raw('1'),'=',DB::raw('town_y - town_x'))
            ->orderBy('town_x')
            ->with(['routes'])
            ->get();
//        dd($townConnections);
        return $townConnections;
    }

}
