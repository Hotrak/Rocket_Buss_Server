<?php

namespace App\Http\Controllers;

use App\Order;
use App\Town;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private $userOrders = 'SELECT o.id,TIME_FORMAT(ro.time , "%H:%i") as time,
        (SELECT  t.name FROM towns t WHERE t.id= tc.town1_id) AS town1_name,
        (SELECT t2.name FROM towns t2 WHERE t2.id= tc.town2_id) AS town2_name,
        sch.date_start,o.order_status
        FROM orders o
              JOIN schedule_routes sr ON sr.id = o.schedule_route_id
              LEFT JOIN routes ro ON ro.id = sr.route_id
              LEFT JOIN schedules sch ON sch.id = sr.schedule_id
              LEFT JOIN town_connections tc ON tc.id = ro.town_connection_id
              where o.user_id=:user_id and order_status < :order_status_max and order_status >= :order_status_min
              order by sch.date_start,ro.time'
         ;

    public function ordersByUserId(Request $request,$userId){
        $orders = DB::select($this->userOrders,['user_id'=>$userId,'order_status_max'=>$request->order_status_max,
            'order_status_min'=>$request->order_status_min]);
        return $orders;
    }


    public function store(Request $request){
        $order = Order::create($request->all());
        return $order;
    }
    public function update(Request $request){
        $order = Order::find($request->id);
        $order->fill($request->all());
        $order->save();
        return $order;
    }
    public function updateStatus(Request $request,$id){
        $order = Order::find($request->id);
        $order->order_status = $request->order_status;
        $order->save();
        return $order;
    }
    public function destroy($id){
        Order::find($id)->delete();
        return response('OK',200);
    }

    public function show($id){
//        $order = DB::select('SELECT TIME_FORMAT(ro.time , "%H:%i") as time,
//        (SELECT  t.name FROM towns t WHERE t.id= tc.town1_id) AS town1_name,
//        (SELECT t2.name FROM towns t2 WHERE t2.id= tc.town2_id) AS town2_name,
//        sch.date_start
//        FROM orders o
//              JOIN schedule_routes sr ON sr.id = o.schedule_route_id
//              LEFT JOIN routes ro ON ro.id = sr.route_id
//              LEFT JOIN schedules sch ON sch.id = sr.schedule_id
//              LEFT JOIN town_connections tc ON tc.id = ro.town_connection_id
//              where o.id=:order_id',['order_id'=>$id]);

        $order = DB::table('orders')
            ->join('schedule_routes','schedule_routes.id','=','orders.schedule_route_id')
            ->join('schedules','schedules.id','=','schedule_routes.schedule_id')
            ->join('drivers','drivers.id','=','schedules.driver_id')
            ->join('cars','cars.id','=','schedules.car_id')
            ->join('car_models','car_models.id','=','cars.model_id')
            ->join('colors','colors.id','=','cars.color_id')
            ->join('routes','routes.id','=','schedule_routes.route_id')
            ->join('town_connections','town_connections.id','=','routes.town_connection_id')
            ->join('points','points.id','=','orders.point_id')
            ->join('users','users.id','=','drivers.user_id')
            ->select('orders.id',
                'orders.count_places',
                'points.name as point',
                'colors.name as color',
                'car_models.name as model',
                'cars.number',
                'users.phone',
                'schedules.date_start',
                'town_connections.price',
                'town_connections.time_drive',
                'town_connections.town1_id',
                'town_connections.town2_id',
                DB::raw('TIME_FORMAT(routes.time , \'%H:%i\') as time')

            )
            ->where("orders.id","=",$id)
            ->get();

        $order[0]->town1 = Town::find($order[0]->town1_id)->name;
        $order[0]->town2 = Town::find($order[0]->town2_id)->name;


        return $order;

    }

    public function ordersByTownConnGroup($townConnGroup,$date){
        $orders = DB::table('orders')
            ->join('schedule_routes','schedule_routes.id','=','orders.schedule_route_id')
            ->join('schedules','schedules.id','=','schedule_routes.schedule_id')
            ->join('drivers','drivers.id','=','schedules.driver_id')
            ->join('cars','cars.id','=','schedules.car_id')
            ->join('car_models','car_models.id','=','cars.model_id')
            ->join('colors','colors.id','=','cars.color_id')
            ->join('routes','routes.id','=','schedule_routes.route_id')
            ->join('town_connections','town_connections.id','=','routes.town_connection_id')
            ->join('points','points.id','=','orders.point_id')
            ->join('users','users.id','=','drivers.user_id')
            ->select('orders.id',
                'orders.count_places',
                'points.name as point',
                'colors.name as color',
                'car_models.name as model',
                'cars.number',
                'users.phone',
                'schedules.date_start',
                'town_connections.price',
                'town_connections.time_drive',
                'town_connections.town1_id',
                'town_connections.town2_id',
                DB::raw('TIME_FORMAT(routes.time , \'%H:%i\') as time')

            )
            ->where("town_connections.conn_group","=",$townConnGroup)
            ->whereDate("schedules.date_start",$date)
            ->get();

        return $orders;

    }
}
