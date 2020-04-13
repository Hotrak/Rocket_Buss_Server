<?php

namespace App\Http\Controllers;

use App\Order;
use App\Town;
use App\TownConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Util\RegularExpressionTest;

class RouteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $towns = TownConnection::all()->fresh(['town1','town2']);
//        foreach ($towns as $town){
//            $town['town1_name'] = $town->town1->name;
//            $town['town2_name'] = $town->town2->name;
////            $town->town1->name;
////            $town->town2->name;
//        }
        return $towns;
    }
    public function times($id,$date,$countPlaces){

        $times = DB::select('SELECT sr.id,TIME_FORMAT(r.time , "%k:%i")as time_road,(c.count_places - (
            SELECT if(SUM(o1.count_places) IS NULL ,0,SUM(o1.count_places))
            FROM orders o1 
            WHERE o1.schedule_id = s.id )) as count_places 
            FROM schedules s 
            LEFT JOIN schedule_routes sr ON sr.schedule_id = s.id 
            LEFT JOIN routes r ON r.id = sr.route_id 
            LEFT JOIN time_orders t ON r.time_order_id = t.id 
            LEFT JOIN cars c ON s.car_id = c.id 
            WHERE s.date_start = :date AND r.town_connection_id = :id AND count_places > :count'
            , ['date'=>$date,'id'=>$id,'count'=>$countPlaces]);

        return $times;
    }

    public function points($id){
        $town = TownConnection::find($id)->town1;
        return $town->points;
    }

    public function order(Request $request){
        $order = Order::create($request->all());
        return $order;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
