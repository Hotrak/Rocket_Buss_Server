<?php

namespace App\Http\Controllers;

use App\CarModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CarModelController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index(){
        return CarModel::all();
    }
    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'name' =>'required|unique:car_models',
        ], [
            'name.required' => 'Название модели является обязательным для заполнения',
            'name.unique' => 'Даная модель уже существует',
        ]);

        if ($validator->fails()) {
            return response(["errors" => $validator->errors()->all()],422);
        }

        $carModel = CarModel::create($request->all(['name']));
        return $carModel;
    }
}
