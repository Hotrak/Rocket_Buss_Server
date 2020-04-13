<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $fillable = [
        'schedule_route_id','point_id','phone','order_status','comment','user_id','count_places','order_source',
    ];

    public function ordersByScheduleId($scheduleId){
        $schedule = DB::table('orders')
            ->join('schedule_routes', 'schedule_routes.id', '=', 'orders.schedule_route_id')
            ->join('schedules', 'schedules.id', '=', 'schedule_routes.schedule_id')
            ->join('points', 'points.id', '=', 'orders.point_id')
            ->select('orders.id',
                'orders.count_places',
                'orders.phone',
                'points.name',
                'orders.order_status'
            )->where('orders.schedule_route_id','=',$scheduleId)
            ->get();
        return $schedule;
    }
}
