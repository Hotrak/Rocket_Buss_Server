<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = [
        'color_id','model_id','number','count_places','end_of_inspection','end_of_insurance'
    ];

    public function color(){
        return $this->belongsTo('App\Color');
    }
    public function model(){
        return $this->belongsTo('App\CarModel');
    }
}
