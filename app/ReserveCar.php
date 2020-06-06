<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReserveCar extends Model
{
    public function car(){
        $car = $this->hasOne('\App\Car','id','car_id');
        return $car;
    }

    public function getRandomByDate($date){

        $reserve = ReserveCar::whereDate('reserve_date', '=',$date)
            ->where('reserve_state','=','0')
            ->get();

        if(count($reserve) != 0) {
            $reserve =$reserve->random();
            $reserve->reserve_state = 1;
            $reserve->save();
            return $reserve;
        }

        return null;
    }
}
