<?php

namespace App\Http\Controllers;

use App\Order;
use App\Schedule;
use App\Settings;
use App\TownConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function statisticsByDayOfWeek(Request $request){
        $statisticsQuery = Order::query();
        if($request->has('dateStart'))
            $statisticsQuery->whereBetween('schedules.date_start',[$request->dateStart,$request->dateEnd]);

        $statisticsQuery->select(
            DB::raw('SUM(orders.count_places) AS full_count_places'),
            DB::raw('SUM(orders.count_places * town_connections.price) AS price'),
            DB::raw('DAYOFWEEK(schedules.date_start) AS date_start_of_w'),
            DB::raw('
                (
                SELECT  CONCAT(town1.name,\'-\',town2.name) AS full_name
                FROM town_connections AS town_conn2
                JOIN towns AS town1 ON town1.id = town_conn2.town1_id
                JOIN towns AS town2 ON town2.id = town_conn2.town2_id
                WHERE town_connections.conn_group = town_conn2.conn_group
                AND town_conn2.town_x = 1 AND town_conn2.town_y =  
                    (
                    SELECT max(town_conn3.town_y)
                    FROM town_connections as town_conn3 
                    WHERE town_conn3.conn_group = town_conn2.conn_group
                    )
                ) AS full_name 
             '),
            DB::raw('
                (
                SELECT max(town_conn2.town_y)
                FROM town_connections as town_conn2
                WHERE town_conn2.conn_group = town_connections.conn_group
                ) AS town_y  
            '),
            DB::raw('
                (
                SELECT  town_conn2.id 
                FROM town_connections AS town_conn2
                WHERE town_connections.conn_group = town_conn2.conn_group
                AND town_conn2.town_x = 1 AND town_conn2.town_y =  
                    (
                    SELECT max(town_conn3.town_y)
                    FROM town_connections as town_conn3 
                    WHERE town_conn3.conn_group = town_conn2.conn_group
                    )
                ) AS town_conn_id
            '),
            'town_connections.conn_group')
            ->join('schedule_routes','schedule_routes.id','=','orders.schedule_route_id')
            ->join('routes','routes.id','=','schedule_routes.route_id')
            ->join('schedules','schedules.id','=','schedule_routes.schedule_id')
            ->join('town_connections','town_connections.id','=','routes.town_connection_id')
            ->groupBy(
                'town_connections.conn_group',
                'date_start_of_w'
            )
            ->orderBy('town_conn_id');

        $statistics = $statisticsQuery->get();
//        dd($statistics);
        $dates = Schedule::select(
            DB::raw(' MAX(date_start) as max_date'),
            DB::raw('MIN(date_start) as min_date')
        )->first();
//        $dates = DB::selectOne('SELECT MAX(date_start) as max_date , MIN(date_start) as min_date from schedules');
        return ['data'=>$statistics,'max_date'=>$dates->max_date,'min_date'=>$dates->min_date];
    }

    public function statisticsByMonths(Request $request){
        $statisticsQuery = Order::query();

        if($request->has('dateStart'))
            $statisticsQuery->whereBetween('schedules.date_start',array($request->dateStart,$request->dateEnd));
        $statisticsQuery->select(
            DB::raw('SUM(orders.count_places) AS full_count_places'),
            DB::raw('SUM(orders.count_places * town_connections.price) AS price'),
            DB::raw('MONTH(schedules.date_start) AS date_start_m'),
            DB::raw('
                (
                SELECT  CONCAT(town1.name,\'-\',town2.name) AS full_name
                FROM town_connections AS town_conn2
                JOIN towns AS town1 ON town1.id = town_conn2.town1_id
                JOIN towns AS town2 ON town2.id = town_conn2.town2_id
                WHERE town_connections.conn_group = town_conn2.conn_group
                AND town_conn2.town_x = 1 AND town_conn2.town_y =  
                    (
                    SELECT max(town_conn3.town_y)
                    FROM town_connections as town_conn3 
                    WHERE town_conn3.conn_group = town_conn2.conn_group
                    )
                ) AS full_name 
             '),
            DB::raw('
                (
                SELECT max(town_conn2.town_y)
                FROM town_connections as town_conn2
                WHERE town_conn2.conn_group = town_connections.conn_group
                ) AS town_y  
            '),
            DB::raw('
                (
                SELECT  town_conn2.id 
                FROM town_connections AS town_conn2
                WHERE town_connections.conn_group = town_conn2.conn_group
                AND town_conn2.town_x = 1 AND town_conn2.town_y =  
                    (
                    SELECT max(town_conn3.town_y)
                    FROM town_connections as town_conn3 
                    WHERE town_conn3.conn_group = town_conn2.conn_group
                    )
                ) AS town_conn_id
            '),
            'town_connections.conn_group')
            ->join('schedule_routes','schedule_routes.id','=','orders.schedule_route_id')
            ->join('routes','routes.id','=','schedule_routes.route_id')
            ->join('schedules','schedules.id','=','schedule_routes.schedule_id')
            ->join('town_connections','town_connections.id','=','routes.town_connection_id')
            ->groupBy(
                'town_connections.conn_group',
                'date_start_m'
            )
            ->orderBy('town_conn_id');

        $statistics = $statisticsQuery->get();
//        dd($statistics);
        $dates = Schedule::select(
            DB::raw(' MAX(date_start) as max_date'),
            DB::raw('MIN(date_start) as min_date')
        )->first();
//        $dates = DB::selectOne('SELECT MAX(date_start) as max_date , MIN(date_start) as min_date from schedules');
        return ['data'=>$statistics,'max_date'=>$dates->max_date,'min_date'=>$dates->min_date];
    }

    public function statisticMenu(Request $request){
        $statisticsQuery = Order::query();
        $statisticsQuery->select(
            DB::raw('SUM(orders.count_places) AS full_count_places'),
            DB::raw('SUM(orders.count_places * town_connections.price) AS price'),
            DB::raw('
                (
                SELECT  CONCAT(town1.name,\'-\',town2.name) AS full_name
                FROM town_connections AS town_conn2
                JOIN towns AS town1 ON town1.id = town_conn2.town1_id
                JOIN towns AS town2 ON town2.id = town_conn2.town2_id
                WHERE town_connections.conn_group = town_conn2.conn_group
                AND town_conn2.town_x = 1 AND town_conn2.town_y =  
                    (
                    SELECT max(town_conn3.town_y)
                    FROM town_connections as town_conn3 
                    WHERE town_conn3.conn_group = town_conn2.conn_group
                    )
                ) AS full_name 
             '),
            DB::raw('
                (
                SELECT max(town_conn2.town_y)
                FROM town_connections as town_conn2
                WHERE town_conn2.conn_group = town_connections.conn_group
                ) AS town_y  
            '),
            DB::raw('
                (
                SELECT  town_conn2.id 
                FROM town_connections AS town_conn2
                WHERE town_connections.conn_group = town_conn2.conn_group
                AND town_conn2.town_x = 1 AND town_conn2.town_y =  
                    (
                    SELECT max(town_conn3.town_y)
                    FROM town_connections as town_conn3 
                    WHERE town_conn3.conn_group = town_conn2.conn_group
                    )
                ) AS town_conn_id
            '),
            'town_connections.conn_group')
            ->join('schedule_routes','schedule_routes.id','=','orders.schedule_route_id')
            ->join('routes','routes.id','=','schedule_routes.route_id')
            ->join('schedules','schedules.id','=','schedule_routes.schedule_id')
            ->rightJoin('town_connections','town_connections.id','=','routes.town_connection_id')
            ->groupBy(
                'town_connections.conn_group'
            )
            ->orderBy('town_conn_id');


//        $statisticsQuery->whereDate('schedules.date_start','<=',date('Y-m-d', strtotime(now(). " - 30 day")));
        $statisticsNow = $statisticsQuery->get();
//        dd($statisticsNow);
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

        return ['statistic'=>$statisticsNow,'work_state'=>$sum];
    }
}

//SELECT drivers.week_end ,(SELECT COUNT(*) FROM drivers) - COUNT(*) AS drivers_count
//  FROM drivers
//  GROUP BY drivers.week_end
//;
