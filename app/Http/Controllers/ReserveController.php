<?php

namespace App\Http\Controllers;

use App\Driver;
use App\ReserveCar;
use App\ReserveDriver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReserveController extends Controller
{
    public function reservesCars(Request $request){
        $reserve = DB::table('reserve_cars')
            ->join('cars', 'reserve_cars.car_id', '=', 'cars.id')
            ->join('colors', 'cars.color_id', '=', 'colors.id')
            ->join('car_models', 'cars.model_id', '=', 'car_models.id')
            ->select('reserve_cars.*',
                'colors.name as color_name',
                'car_models.name as model_name','cars.number','cars.count_places')
            ->whereDate('reserve_date',$request->date)
            ->get();

        return $reserve;
    }
    public function reservesDrivers(Request $request){
        $reserve =  DB::table('reserve_drivers')
            ->join('drivers', 'reserve_drivers.driver_id', '=', 'drivers.id')
            ->join('users', 'users.id', '=', 'drivers.user_id')
            ->select('reserve_drivers.*',
                'drivers.name',
                'drivers.surname',
                'drivers.patronymic',
                'users.phone'
                )
            ->whereDate('reserve_date',$request->date)
            ->get();

        return $reserve;

    }

    public function destroyCar($id){
        ReserveCar::find($id)->delete();
    }
    public function destroyDriver($id){
        ReserveDriver::find($id)->delete();
    }
}
