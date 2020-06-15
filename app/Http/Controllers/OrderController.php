<?php

namespace App\Http\Controllers;

use App\Car;
use App\Order;
use App\Point;
use App\Schedule;
use App\Town;
use App\TownConnection;
use http\Client\Curl\User;
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
//        $orders = DB::select($this->userOrders,['user_id'=>$userId,'order_status_max'=>$request->order_status_max,
//            'order_status_min'=>$request->order_status_min])->paginate(5);

        $order = new Order();
        $orders = $order->ordersByUserId($userId,$request->order_status_max,$request->order_status_min,$request->count);
//        $orders = Order::where('orders.user_id','=',$userId)
//            ->where('orders.order_status','<',$request->order_status_max)
//            ->where('orders.order_status','>=',$request->order_status_min)
//            ->select(
//                'orders.id',
//                'schedules.date_start',
//                'orders.order_status',
//                DB::raw('TIME_FORMAT(routes.time , "%H:%i") as time'),
//                'town1.name as town1_name',
//                'town2.name as town2_name'
//            )
//            ->join('schedule_routes','schedule_routes.id','=','orders.schedule_route_id')
//            ->join('routes','routes.id','=','schedule_routes.route_id')
//            ->join('schedules','schedules.id','=','schedule_routes.schedule_id')
//            ->join('town_connections','town_connections.id','=','routes.town_connection_id')
//            ->join('towns as town1','town1.id','=','town_connections.town1_id')
//            ->join('towns as town2','town2.id','=','town_connections.town2_id')
//            ->orderBy('schedules.date_start')
//            ->paginate($request->count);
        return $orders;
    }

    public function store(Request $request){

//        return $request->schedule_route_id;

        $schedule = new Schedule();
        $scheduleWithCountPlaces = $schedule->singleRouteWithCountPlaces($request->schedule_route_id);
        $accessCountPlaces = $scheduleWithCountPlaces->all_places - $scheduleWithCountPlaces->count_places;

        if($request->count_places > $accessCountPlaces)
            return response(['message'=>'Недостаточно мест'],422);
        else{
            if($request->count_places == $accessCountPlaces){
                \App\User::notifyAllAdmins(['message'=>"На маршрут ".$scheduleWithCountPlaces->town1_name." ".$scheduleWithCountPlaces->town2_name." в ".$scheduleWithCountPlaces->time." закочились места"]);
            }
        }


//        if(isset($request->user_id)){
//
//            $price = DB::table('town_connections')
//                ->select('town_connections.price')
//                ->join('routes','routes.town_connection_id','=','town_connections.id')
//                ->join('schedule_routes','schedule_routes.route_id','=','routes.id')
//                ->where('schedule_routes.id','=',$request->schedule_route_id)//schedule_route_id
//                ->first()->price;
//
//            $user = \App\User::find($request->user_id);
//            $user->score = $user->score + ($price*10);
//            $user->save();
//        }
        if($request->user_id == -1){
            $oldUser = \App\User::where('telegram_id','=',$request->telegram_id)->first();
            $request['user_id'] = $oldUser->id;
            $request['phone'] = $oldUser->phone;
        }
        if($request->point_id == 0){
            $request['point_id'] = Point::where('town_id','=',$scheduleWithCountPlaces->town1_id)->first()->id;
        }
        $countPlaces = $request->count_places;
        $order = '';
        for($i=0;$i< $countPlaces;$i++){
            $request['count_places'] = 1;
            $order = Order::create($request->all());
        }

        $order->count_places = $countPlaces;

//        if($request->order_source == 1 || $request->order_source == 2){
//
//            return $order;
//            $aboutOrder = new Order();
//            $aboutOrder = $aboutOrder->orderById($order->id);
//            $aboutOrder = $aboutOrder[0];
//
//            $user = new \App\User();
//            $message = "Rocket Bus $aboutOrder->date_start $request->point_time мест:$aboutOrder->count_places ост:$aboutOrder->point";
//            $user->sendSms($request->phone,$message);
//        }

        $oldInfo = new Order();
        $oldInfo = $oldInfo->orderById($order->id)[0];

        return ["order"=>$order,"order_info" => $oldInfo];
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

//        if(isset($order->user_id)){
//
//            if($request->order_status!=3 && $request->order_status!=4&& $request->order_status!=5){
//
//                $price = DB::table('town_connections')
//                    ->select('town_connections.price')
//                    ->join('routes','routes.town_connection_id','=','town_connections.id')
//                    ->join('schedule_routes','schedule_routes.route_id','=','routes.id')
//                    ->where('schedule_routes.id','=',$request->schedule_route_id)//schedule_route_id
//                    ->first()->price;
//
//                $user = \App\User::find($order->user_id);
//
//                if($order->order_status == 6){
//                    $user->score = $user->score +200;
//                }else if($order->order_status == 1){
//                    $user->score = $user->score - ($price*10);
//                }
//
//                if($request->order_status == 6){
//                    $user->score = $user->score -200;
//                }else if($request->order_status == 1){
//                    $user->score = $user->score + ($price*10);
//                }
//
//                $user->save();
//            }

//        }
//
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

        $order = new Order();
        return  $order->orderById($id);

    }

    public function ordersByTownConnGroup($townConnGroup,$scheduleId){
        $orders = new Order();

        $orders = $orders->ordersByTownConnGroup($scheduleId);
        $townConn = TownConnection::getByConnGroup($townConnGroup);
        $car = Car::where('schedules.id','=',$scheduleId)
            ->join('schedules','schedules.car_id','=','cars.id')
            ->first();

        return ['orders'=>$orders,'town_connections'=>$townConn,'car'=>$car];

    }

    public function telegramUserOrders($id){
        $oldUser = \App\User::where('telegram_id','=',$id)->first();
        $order = new Order();
        $orders = $order->ordersByUserId($oldUser->id,2,0,20);
        return $orders;
    }
}
