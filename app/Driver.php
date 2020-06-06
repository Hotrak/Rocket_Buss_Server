<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Driver extends Model
{
    protected $fillable = [
        'name','surname','patronymic','birthday','med_exam','town','street','home_num','week_end'
    ];

    public function user(){
        return $this->hasOne('App\user','id', 'user_id');
    }

    public function checkWorkTime($id,$date, $time_drive){
        $workTime = DB::select('SELECT SUM(town_connections.time_drive) AS all_time_drive
          FROM schedules
          JOIN schedule_routes ON schedule_routes.schedule_id = schedules.id
          JOIN routes ON routes.id = schedule_routes.route_id
          JOIN town_connections ON town_connections.id = routes.town_connection_id
          WHERE schedules.driver_id = :id AND
          schedules.date_start = :date AND 
          town_connections.town_x = 1 AND 
          town_connections.town_y = 
          (SELECT MAX(tc2.town_y) 
            FROM town_connections tc2 
            WHERE town_connections.conn_group = tc2.conn_group)',['date'=>$date,'id'=>$id]);
        $workTime = ($workTime[0]->all_time_drive+$time_drive)/60;

        return $workTime<=8;
    }

    public function checkFreeTime($id,$date,$time,$timeDrive){

        if(!$this->checkReserve($id,$date))
            return false;

        $result =  DB::select('SELECT routes.id,routes.time,town_connections.is_back,
            town_connections.time_drive
          FROM schedules
          JOIN schedule_routes ON schedule_routes.schedule_id = schedules.id
          JOIN routes  ON routes.id = schedule_routes.route_id
          JOIN town_connections ON town_connections.id = routes.town_connection_id
         
          WHERE schedules.driver_id = :id AND
          schedules.date_start = :date AND 
          town_connections.town_x = 1 AND  town_connections.town_y =
            (SELECT MAX(tc2.town_y) 
            FROM town_connections tc2 
            WHERE town_connections.conn_group = tc2.conn_group)
        
          AND (routes.id = 
         (SELECT r2.id 
          FROM routes r2  
          WHERE r2.time >= :time1
            AND r2.id = routes.id
          GROUP BY r2.id
          ORDER BY r2.time
          LIMIT 1)
          OR routes.id = 
         (SELECT r2.id 
          FROM routes r2  
          WHERE r2.time <= :time2
            AND r2.id = routes.id
          GROUP BY r2.id
          ORDER BY r2.time
          LIMIT 1))
          order by routes.time
          ',['id'=>$id,'date'=>$date,'time1'=>$time,'time2'=>$time]);

        $currentTimeEnd = date("H:i:s",strtotime($time) + ($timeDrive+60)*60);
        $timeEnd = '';

//        dd($result);

        foreach ($result as $item){
            $timeStart = $item->time;
            if($item->is_back == 0){
                $timeEnd = date("H:i:s",strtotime($item->time) + ($item->time_drive*2+60)*60);
//                dd($timeEnd);
            }else{
                $timeStart = date("H:i:s",strtotime($item->time) - ($item->time_drive+60)*60);
                $timeEnd = $item->time;
            }
            if($timeStart <= $time && $timeEnd >= $time){
//                var_dump("$id = $timeStart <= $time and $timeEnd >= $time");
//                echo '<br>';
                return false;
            }


            if($timeStart <= $currentTimeEnd && $timeEnd >= $currentTimeEnd){
//                var_dump("$id = $timeStart <= $currentTimeEnd and $timeEnd >= $currentTimeEnd");
//                echo '<br>';
                return false;
            }
        }
        return true;

    }

    function checkReserve($id,$date){
        $isReserve = ReserveDriver::whereDate('reserve_date','=',$date)
            ->where('driver_id','=',$id)->first();
        if(isset($isReserve)){
            return false;
        }
        return true;
    }

    public function checkMedExam($id,$date){
        $driver = Driver::find($id);
        return $driver->med_exam > $date;
    }
}
