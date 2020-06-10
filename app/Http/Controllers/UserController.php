<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Notifications\WelcomeMail;
use App\ReserveCar;
use App\Role;
use App\Settings;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
//        $this->middleware('role:admin');
    }

    public function index(){
        $user = auth()->user();
        $user->roles;

        $settings = Settings::all();

        return response()->json(['user' => $user,'settings'=>$settings],200);
    }

    public function authTelegram(Request $request){

        $oldUser = User::where('telegram_id','=',$request->telegram_id)->first();
        if(!isset($oldUser)){
            $user = new User();
            $user->name = $request->name;
            $user->password = '8563215';
            $user->phone = $request->phone;
            $user->telegram_id = $request->telegram_id;
            $user->save();
            return $user;
        }


        return $oldUser;
    }

    public function driver(){
        $user = auth()->user();
        $user->roles;
        $user->driver;
        return json_encode($user);
    }
    public function login(){
        if(!auth()->attempt(request(['phone','password'])))
        {
            $response = "Не верно введён логин или пароль";
            return response($response, 422);
        }

        $user = auth()->user();
//        $user->notify(new WelcomeMail());

        if($user->lock==1){
            $response = "Ваш аккаунт заблокирован";
            return response($response, 422);
        }

        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = ['token' => $token];
        return response($response, 200);
    }

    public function driverLogin(){
        if(!auth()->attempt(request(['phone','password'])))
        {
            $response = "Не верно введён логин или пароль";
            return response($response, 422);
        }

        $user = auth()->user();
//        $user->notify(new WelcomeMail());

        if($user->hasRole('driver')){
            $token = $user->createToken('Laravel Password Grant Client')->accessToken;
            $response = ['token' => $token];
            return response($response, 200);
        }else{
            $response = "Не верно введён логин или пароль";
            return response($response, 422);
        }


    }

    public function store(UserRequest $authRequest){
        $validated = $authRequest->validated();

        $authRequest['password']=Hash::make($authRequest->password);
        $user = User::create($authRequest->all(['name','phone','password']));

        $role = Role::find(3);
        $user->setRole($role);

        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = ['token' => $token];

        return response($response, 200);
    }
    public function update(UserRequest $authRequest, $id){
        $validated = $authRequest->validated();

        $user = User::find($id);
        $user->name = $authRequest->name;
        $user->phone = $authRequest->phone;
        if($authRequest->password != 'default'){
            $user->password = Hash::make($authRequest->password);
        }
        $user->save();
//        $accessToken = $user->token();
//        $accessToken->revoke();

        return response($user, 200);
    }
    public function store2(Request $request){

        $rules=[
            'email' =>'required|unique:users|email',
            'password' =>'required|min:6|max:40|confirmed ',
            'phone' =>'required|unique:phone',
            'name' =>'required',
        ];
        $messages = [
            'phone.required' => 'Телефон является обязательным для заполнения',
            'phone.unique' => 'Пользователь с данным Телефонам уже зарегистрирован',
            'name.required' => 'Имя является обязательным для заполнения',
            'password.required'  => 'Пароль является обязательным для заполнения',
            'password.confirmed'  => 'Пароли не совподяют',
            'password.min'  => 'Пароль слишком лёгкий',
            'password.max'  => 'Пароль слишком длинный',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response(["token" => $validator->errors()->all()],422);
        }

        $request['password']=Hash::make($request->password);
        $user = User::create($request->all(['name','phone','password']));
//        auth()->login($user);

        $role = Role::find(3);
        $user->setRole($role);

        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = ['token' => $token];

        return response($response, 200);
    }

    public function clients(){
        $user = new User();
        return $user->clients();
    }

    public function updateState(Request $request,$id){
        $user = User::find($id);
        $user->lock = $request->lock;
        $user->save();
        return $user;
    }

    public function logout(Request $request){
        $accessToken = Auth::user()->token();
        $accessToken->revoke();
//        if (Auth::check()) {
//            Auth::user()->AuthAcessToken()->delete();
//        }
        return response()->json(['token'=>$accessToken],200);
    }


}
