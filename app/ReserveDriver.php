<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReserveDriver extends Model
{
    public function driver(){
        return $this->hasOne('\App\Driver','id','driver_id');
    }

    public function getRandomByDate($date){

        $reserve = ReserveDriver::whereDate('reserve_date', '=',$date)
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
