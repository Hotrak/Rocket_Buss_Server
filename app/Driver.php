<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'name','surname','patronymic','birthday','med_exam','town','street','home_num','week_end'
    ];

    public function user(){
        return $this->hasOne('App\user','id', 'user_id');
    }
}
