<?php

namespace App\Http\Controllers;

use App\Driver;
use App\Http\Requests\DriverRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DriverController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index(){
        $drivers = Driver::all()->fresh('user');
        return $drivers;
    }
    public function store(DriverRequest $request){

        $validated = $request->validated();
        $request['password']=Hash::make($request->password);
        $user = User::create($request->all(['name','email','phone','password']));

        $driver = Driver::create($request->all(['name','surname','patronymic','birthday','med_exam','town','street','home_num','week_end']));
        $driver->user_id = $user->id;
        $driver->save();
        $driver->user;
        return $driver;
    }

    public function update(DriverRequest $request,$id){
        $validated = $request->validated();

        $driver = Driver::find($id);
        $driver->name = $request->name;
        $driver->surname = $request->surname;
        $driver->patronymic = $request->patronymic;
        $driver->med_exam = $request->med_exam;
        $driver->birthday = $request->birthday;
        $driver->town = $request->town;
        $driver->street = $request->street;
        $driver->home_num = $request->home_num;
        $driver->week_end = $request->week_end;
        $driver->save();
        $driver->user;

        return $driver;
    }

    public function destroy($id){
        Driver::find($id)->delete();
    }
}
