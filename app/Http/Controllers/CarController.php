<?php

namespace App\Http\Controllers;

use App\Car;
use App\Http\Requests\CarRequest;
use App\ReserveCar;
use App\Schedule;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index(){
        return Car::all()->fresh(['color','model']);
    }

    public function store(CarRequest $request){
        $validated = $request->validated();

        $car = Car::create($request->all());
        $car->color;
        $car->model;
        return $car;
    }
    public function update(CarRequest $request,$id){
        $validated = $request->validated();

        $car = Car::find($id);
        $car->color_id = $request->color_id;
        $car->model_id = $request->model_id;
        $car->number = $request->number;
        $car->count_places = $request->count_places;
        $car->end_of_inspection = $request->end_of_inspection;
        $car->end_of_insurance = $request->end_of_insurance;
        $car->save();

        $car->color;
        $car->model;
        return $car;
    }
    public function updateState(Request $request,$id){
        $car = Car::find($id);
        $car->state = $request->state;
        $car->save();

        if($request->state == 1){
            return $car;
        }

        $reserveCar =  ReserveCar::all()->where('reserve_state','=','0')->random();

        $reserveCar->reserve_state = 1;
        $reserveCar->save();

        Schedule::where('car_id','=',$car->id)->whereDate('date_start','>=',now())->update(['car_id'=> $reserveCar->car_id]);

        return $reserveCar;
    }
}
