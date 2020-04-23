<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','phone', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function AuthAcessToken(){
        return $this->hasMany('\App\OauthAccessToken');
    }

    public function hasRole($roleName){

        foreach ($this->roles as $role) {
            if($roleName == $role->name)
                return true;
        }
        return false;
    }
    public function roles(){
        $roles = $this->belongsToMany('\App\Role');
        return $roles;
    }
    public function setRole($role){
        $userHasRole = new RoleUser();
        $userHasRole->role_id = $role->id;
        $userHasRole->user_id = $this->id;
        $userHasRole->save();
    }

    public function driver(){
        return $this->hasOne('\App\Driver');
    }
}
