<?php

namespace App\Http\Controllers;

use App\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function ordersByUserId($userId){

        $schedule = DB::select('SELECT TIME_FORMAT(ro.time , "%k:%i") as time,(SELECT  t.name FROM towns t WHERE t.id= tc.town1_id) AS twon1,(SELECT t2.name FROM towns t2 WHERE t2.id= tc.town2_id) AS twon2 FROM orders o
              JOIN schedule_routes sr ON sr.id = o.schedule_route_id
              LEFT JOIN routes ro ON ro.id = sr.route_id
              LEFT JOIN town_connections tc ON tc.id = ro.town_connection_id');
        return $schedule;
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
}
