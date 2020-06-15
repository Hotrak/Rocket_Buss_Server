<?php

namespace App\Http\Controllers;

use App\Car;
use App\Driver;
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

    public function index(Request $request){

        $carQuery = Car::query();
        $carQuery->join('car_models','car_models.id','=','cars.model_id')
            ->join('colors','colors.id','=','cars.color_id')
            ->select('cars.*','car_models.name as model_name','colors.name as color_name');

        if($request->has('search')){
            foreach (['cars.number','car_models.name','colors.name','cars.count_places'] as $item){
                $carQuery->orWhere($item,'like','%'.$request->search.'%');
            }
        }
        return $carQuery->paginate(10);
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

        $car['color_name'] = $car->color->name;
        $car['model_name'] = $car->model->name;

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

    public function destroy($id){
        Car::find($id)->delete();
    }
}
