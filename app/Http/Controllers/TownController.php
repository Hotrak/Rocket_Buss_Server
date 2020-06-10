<?php

namespace App\Http\Controllers;

use App\Point;
use App\Town;
use App\TownConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TownController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin')->except('points');
    }
    public function index(){
        return Town::all();
    }
    public function points($townConnectionId){
        $townConnection = TownConnection::find($townConnectionId);
        if($townConnection->is_transit == true && $townConnection->town_x != 1){
            $points = $townConnection->town1->points->where('is_transit',1);
            $oldPoints = $points;
            $points = [];
            foreach ($oldPoints as $item){
                $points[] = $item;
            }
//            dd($points);

        }
        else{
            $points = $townConnection->town1->points;
        }
        return $points;
    }
    public function allPoints($townId){
        return $points = Town::find($townId)->points;
    }
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name' =>'required|unique:towns'
        ],[
            'name.required'=>'Поле Новый город обязательно для заполнения',
            'name.unique'=>'Город уже существует'
        ]);

        if ($validator->fails()) {
            return response(["errors" => $validator->errors()->all()],422);
        }
        $town = Town::create($request->all());

        foreach ($request->points as $item){
            $point = new Point();
            $point->name = $item['name'];
            $point->town_id = $town->id;
            $point->is_transit = $item['isPointTrans'];
            $point->point_time = $item['pointTime'];
            if(isset($item['pointTimeTrans']))
                $point->point_time_transit = $item['pointTimeTrans'];
            $point->coords = json_encode($item['coords']);
            $point->x = $item['coords'][0];
            $point->y = $item['coords'][1];
            $point->save();
        }
        return $town;
    }

    public function update(Request $request, $id){
        $town = Town::find($id);
//        $town->name = $request['town'];
//        $town->save();

        foreach ($request->points as $item){
            $point = null;
            if(isset($item['id'])){
                $point = Point::find($item['id']);

            }else{
                $point = new Point();
            }
            $point->name = $item['name'];
            $point->x = $item['x'];
            $point->y = $item['y'];
            $point->town_id = $town->id;
            $point->point_time = $item['point_time'];
            if(isset($item['point_time_transit']))
                $point->point_time_transit = $item['point_time_transit'];
            $point->is_transit = $item['is_transit'];
            $point->save();

        }

    }
}
