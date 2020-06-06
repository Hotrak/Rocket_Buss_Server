<?php

namespace App\Http\Controllers;

use App\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function statistics(Request $request){
        $where = '';
        if(isset($request->dateStart))
            $where =" WHERE schedules.date_start BETWEEN '$request->dateStart' and '$request->dateEnd' ";

        $statistics =  DB::select('SELECT CONCAT(town1.name,\'-\',town2.name) AS full_name,
            town_connections.id,
          town1.name AS town1_name,
          town2.name AS town2_name,
          SUM(orders.count_places) AS count_places,
          town_connections.price,
          schedules.date_start,
          DAYOFWEEK(schedules.date_start) AS date_start_of_w,
        
          MONTH(schedules.date_start) AS date_start_m
        
          FROM town_connections 
          
          
          JOIN towns AS town1 ON town1.id = town_connections.town1_id
          JOIN towns AS town2 ON town2.id = town_connections.town2_id
          JOIN routes ON routes.town_connection_id = town_connections.id
          JOIN schedule_routes ON schedule_routes.route_id = routes.id
          JOIN schedules ON schedules.id = schedule_routes.schedule_id
          JOIN orders ON orders.schedule_route_id = schedule_routes.id
        
         '.$where.'
        
          GROUP BY
          town_connections.id, 
          town1.name,
          town2.name, 
          town_connections.price,
          schedules.date_start,
          date_start_of_w,
      
          full_name,
          date_start_m
          
          ORDER BY town_connections.id
          ');

        $dates = DB::select('SELECT MAX(date_start) as max_date , MIN(date_start) as min_date from schedules');
//        dd($dates[0]->max_date);

        return ['data'=>$statistics,'max_date'=>$dates[0]->max_date,'min_date'=>$dates[0]->min_date];
    }

    public function statisticMenu(){
        $statistic = DB::select('SELECT town_connections.id,
            CONCAT(town1.name,\'-\',town2.name) AS full_name,
                town_connections.town_x,
          town_connections.town_y,
          
          SUM(orders.count_places) AS count_places,
          town_connections.price
        
          FROM town_connections 
         
          JOIN towns AS town1 ON town1.id = town_connections.town1_id
          JOIN towns AS town2 ON town2.id = town_connections.town2_id
          JOIN routes ON routes.town_connection_id = town_connections.id
          JOIN schedule_routes ON schedule_routes.route_id = routes.id
          JOIN schedules ON schedules.id = schedule_routes.schedule_id
          LEFT JOIN orders ON orders.schedule_route_id = schedule_routes.id
        
            
            AND YEAR(schedules.date_start) = YEAR(NOW())
              GROUP BY
              town_connections.id, 
              town1.name,
              town2.name, 
              town_connections.price,
                  town_connections.town_x,
          town_connections.town_y,
             
              full_name
            
              ORDER BY town_connections.id');

        $workTime =  DB::select('SELECT  SUM(tc1.time_drive) AS time_drive,route_days.weekday FROM routes 
          JOIN town_connections tc1 ON tc1.id = routes.town_connection_id
          JOIN route_days ON route_days.route_days_group = routes.route_days_group
          WHERE tc1.town_x = 1 AND tc1.town_y = 
          (SELECT MAX(tc2.town_y) FROM town_connections tc2 WHERE tc1.conn_group = tc2.conn_group)
          GROUP BY route_days.weekday');

//        WHERE WEEKOFYEAR('2020-05-27') - WEEKOFYEAR(schedules.date_start) = 1
//        dd($workTime);

        $sum = 0;
        foreach ($workTime as $item){
            $sum+= $item->time_drive;
        }
        $sum = ceil($sum/60/7/6);

        $reserveDriversCount = Settings::where('name','=','MAX_DRIVER_RESERVES')->first();

        $sum = $sum+$reserveDriversCount->value;

        return ['statistic'=>$statistic,'work_state'=>$sum];
    }
}

//SELECT drivers.week_end ,(SELECT COUNT(*) FROM drivers) - COUNT(*) AS drivers_count
//  FROM drivers
//  GROUP BY drivers.week_end
//;
