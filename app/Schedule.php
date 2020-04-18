<?php

namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Schedule extends Model
{
    public function scheduleShort($townConnectionId,$date){

        $today_dt = new DateTime();
        $expire_dt = new DateTime($date);

        $time = "00:00:00";
        if($today_dt->format("Y-m-d") == $expire_dt->format("Y-m-d")) {
//            $time = $today_dt->format("H:i").":00";
            $time = "07:00:00";
        }


        $schedule = DB::table('schedules')
            ->join('schedule_routes', 'schedule_routes.schedule_id', '=', 'schedules.id')
            ->join('routes', 'routes.id', '=', 'schedule_routes.route_id')
            ->join('town_connections', 'town_connections.id', '=', 'routes.town_connection_id')
            ->join('cars', 'cars.id', '=', 'schedules.car_id')
            ->select('schedule_routes.id',
                'schedules.id as schedule_id',
                DB::raw('TIME_FORMAT(routes.time , \'%H:%i\') as time'),
                DB::raw('SUM(cars.count_places) as all_places '),
                'town_connections.town_x',
                'town_connections.town_y',
                'town_connections.conn_group'
            )
            ->where('town_connections.id','=',$townConnectionId)
            ->where('routes.time','>',$time)
            ->whereDate('schedules.date_start',$date)
            ->groupBy('schedule_routes.id',
            'schedule_id',
            'town_connections.town_x',
            'town_connections.town_y',
            'town_connections.conn_group')
            ->orderBy('time')
            ->get();
        return $schedule;
    }
    public function schedule($townConnectionId,$date){
        $schedule = DB::table('schedules')
            ->join('schedule_routes', 'schedule_routes.schedule_id', '=', 'schedules.id')
            ->join('routes', 'routes.id', '=', 'schedule_routes.route_id')
            ->join('town_connections', 'town_connections.id', '=', 'routes.town_connection_id')
            ->join('drivers', 'drivers.id', '=', 'schedules.driver_id')
            ->join('cars', 'cars.id', '=', 'schedules.car_id')
            ->join('colors', 'colors.id', '=', 'cars.color_id')
            ->join('car_models', 'car_models.id', '=', 'cars.model_id')
            ->select('schedule_routes.id',
                'schedules.id as schedule_id',
                'drivers.name',
                'drivers.surname',
                'colors.name as color',
                'car_models.name as model',
                DB::raw('TIME_FORMAT(routes.time , \'%H:%i\') as time'),
                'cars.count_places as all_places',
                'town_connections.town_x',
                'town_connections.town_y',
                'town_connections.conn_group',
                'cars.state'
            )
            ->where('town_connections.id','=',$townConnectionId)
            ->where('schedules.date_start','=',$date)
            ->orderBy('time')
            ->get();
        return $schedule;
    }
    public function scheduleShortWithCountPlaces($townConnectionId,$date){
        $schedule = $this->scheduleShort($townConnectionId,$date);
        $result = $this->getWithCountPlaces($schedule);
        if(count($result) == 0)
            return [];
        $scheduleGrouped = [];

        $groupedCount = 0;
        $temp = $result[0];
        $scheduleGrouped[] = $result[0];
        for($i = 1;$i< count($result);$i++ ){

            if($result[$i]->time == $temp->time){
                $scheduleGrouped[$groupedCount]->all_places += $result[$i]->all_places;
                $scheduleGrouped[$groupedCount]->count_places += $result[$i]->count_places;

            }else{

                $scheduleGrouped[] = $result[$i];
                $groupedCount++;
            }


            $temp = $result[$i];
        }


        return $scheduleGrouped;
    }
    public function scheduleWithCountPlaces($townConnectionId,$date){

        $schedule = $this->schedule($townConnectionId,$date);
        return $this->getWithCountPlaces($schedule);
    }

    public function getWithCountPlaces($schedule){
        $townX = [];
        $townY = [];
        $scheduleRoutes = [];
        $connectionGroup = 0;
        foreach ($schedule as $item){
            $townX = $item->town_x;
            $townY = $item->town_y;
            $connectionGroup = $item->conn_group;
            $scheduleRoutes[] = $item->id;
        }

        $scheduleIds = DB::table('schedules')
            ->select('schedules.id')
            ->join('schedule_routes','schedule_routes.schedule_id','=','schedules.id')
            ->whereIn('schedule_routes.id',$scheduleRoutes)
            ->get()->map(function ($item){
                return $item->id;
            });
        $orders = DB::table('schedule_routes')
            ->rightJoin('orders','orders.schedule_route_id','=','schedule_routes.id')
            ->join('routes','routes.id','=','schedule_routes.route_id')
            ->join('town_connections','town_connections.id','=','routes.town_connection_id')
            ->whereIn('schedule_routes.schedule_id',$scheduleIds)
            ->where('town_connections.conn_group','=',$connectionGroup)
            ->where('orders.order_status','<',3)
            ->select(DB::raw('sum(orders.count_places) as count_places'),
                'schedule_routes.schedule_id',
                'schedule_routes.id as schedule_route_id',
                'town_connections.town_x',
                'town_connections.town_y',
                'routes.time'
            )
            ->groupBy(['orders.count_places',
                'schedule_routes.schedule_id',
                'town_connections.town_x',
                'town_connections.town_y',
                'routes.time',
                'schedule_routes.id'])
            ->orderBy('schedule_routes.schedule_id')
            ->get();
        $spliceItems = $this->spliceItemsById($orders);

        $results = [];
        $count = 0;
        foreach ($spliceItems as $item){
            $validItems = $this->getItems($item,$townX,$townY);
            if(count($validItems)!= 0){
                $sum = $this->getItemsSum($validItems,$townX,$townY);
                $results[$count]['id'] = $validItems[0]->schedule_id;
                $results[$count]['value'] = $sum;
                $count++;
            }

        }
        for($i = 0;$i< count($schedule);$i++){
            $isSet = false;
            for($j = 0;$j< count($results);$j++){
                if($schedule[$i]->schedule_id == $results[$j]['id']){
                    $schedule[$i]->count_places = $results[$j]['value'];
                    $isSet = true;
                    break;
                }
            }
            if(!$isSet)
                $schedule[$i]->count_places = 0;
        }
        return $schedule;
    }


    public function getItems($orders,$x,$y){
        $result = [];

        foreach ($orders as $item){
            if($item->town_y > $x &&  $item->town_x < $y)
                $result[]= $item;
        }
        return  $result;
    }
    public function getItemsSum($orders,$x,$y){

        $result = [];
        $count = 0;
        for($i = $x; $i< $y;$i++){
            $result[$count] = 0;
            $items = $this->getItems($orders,$i,$i+1);
            foreach ($items as $item){
                $result[$count] += $item->count_places;
            }
            $count++;
        }

        return  max($result);
    }
    public function spliceItemsById($orders){
        $result = [];
        $tempItem = null;
        $count = 0;
        foreach ($orders as $item){
            if($tempItem != null){
                if($item->schedule_id != $tempItem->schedule_id){
                    $count++;
                }
            }
            $result[$count][]= $item;
            $tempItem = $item;
        }
        return  $result;
    }

    public function car(){
        return $this->hasOne('App\Car','id','car_id');
    }
    public function driver(){
        return $this->hasOne('App\Driver','id','driver_id');
    }
}
