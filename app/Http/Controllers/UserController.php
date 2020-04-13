<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Notifications\WelcomeMail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(){
        $user = auth()->user();
        $user->roles;
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

        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = ['token' => $token];
        return response($response, 200);
    }

    public function store(UserRequest $authRequest){
        $validated = $authRequest->validated();

        $authRequest['password']=Hash::make($authRequest->password);
        $user = User::create($authRequest->all(['name','phone','password']));

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

        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = ['token' => $token];

        return response($response, 200);
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
