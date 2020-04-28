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
        }
        else{
            $points = $townConnection->town1->points;
        }

        return $points;
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
}
