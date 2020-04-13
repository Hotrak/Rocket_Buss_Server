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
}
