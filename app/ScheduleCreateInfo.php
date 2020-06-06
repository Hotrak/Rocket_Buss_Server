<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleCreateInfo extends Model
{
    public function getByDate($date){
        $scheduleCreateInfo = ScheduleCreateInfo::whereDate('schedule_date','=',$date)->first();

        $isTodayScheduleCreateInfo = false;

        $currentScheduleCreateInfo = null;
        if(isset($scheduleCreateInfo)){
            $currentScheduleCreateInfo = $scheduleCreateInfo;
            $isTodayScheduleCreateInfo = true;

        } else {
            $yesterdayDate = date('Y-m-d',strtotime($date." - 1 day"));
            $scheduleCreateInfo = ScheduleCreateInfo::whereDate('schedule_date','=',$yesterdayDate)->first();
            if(isset($scheduleCreateInfo)){
                $currentScheduleCreateInfo = $scheduleCreateInfo;
            }
        }

        return $currentScheduleCreateInfo;
    }
}
