<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TownConnection extends Model
{
    protected $fillable = [
        'town1_id','town2_id','time_drive','price'
    ];
    public function town1(){
        return $this->hasOne('App\Town', 'id', 'town1_id');
    }
    public function town2(){
        return $this->hasOne('App\Town', 'id', 'town2_id');
    }
    public static function getByConnGroup($connGroup){
        return TownConnection::where('conn_group','=',$connGroup)
            ->join('towns as town1','town1.id','=','town_connections.town1_id')
            ->join('towns as town2','town2.id','=','town_connections.town2_id')
            ->select('town_connections.*','town1.name as town1_name','town2.name as town2_name')
            ->get();
    }
}
