<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Town extends Model
{
    protected $fillable = [
        'name'
    ];
    public function points(){
        return $this->hasMany('App\Point');
    }
}
