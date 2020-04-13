<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReserveCar extends Model
{
    public function car(){
        $car = $this->hasOne('\App\Car','id','car_id');
        return $car;
    }
}
