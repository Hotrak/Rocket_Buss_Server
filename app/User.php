<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\HasApiTokens;
use App\SmsBy;


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

    public function clients($count){
        $clients =  DB::table('users')
            ->join('role_user','role_user.user_id','=','users.id')
            ->join('roles','roles.id','=','role_user.role_id')
            ->where('roles.name','=','client')
            ->select('users.id','users.phone','users.name','users.score','users.lock')
            ->paginate($count);

        return $clients;
    }

    public function sendSms($phone,$message){

        $phone  = preg_replace('/[^0-9]/', '', $phone);
//        $token = '';//ffb3aefa48dbe35099a14099b403531d
        $token = 'a6403fcdc7a60e84427fbb05ce60f98e';//a6403fcdc7a60e84427fbb05ce60f98e

        $sms        = new \App\SmsBy($token);
        $res        = $sms->createSMSMessage($message);
        $message_id = $res->message_id;
        $res2       = $sms->sendSms($message_id, $phone);

        return $res2;
    }

    public static function notifyAllAdmins($data){
        $admins = User::all()->filter(function ($user){
            return $user->hasRole('admin');
        });
        Notification::send($admins,new \App\Notifications\TestNotificaton($data));
    }
}
