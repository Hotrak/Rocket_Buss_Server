<?php

namespace App\Http\Controllers;

use App\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ColorController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index(){
        return Color::all();
    }
    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'name' =>'required|unique:colors',
        ], [
            'name.required' => 'Название цвета является обязательным для заполнения',
            'name.unique' => 'Данный цвет уже существует',
        ]);

        if ($validator->fails()) {
            return response(["errors" => $validator->errors()->all()],422);
        }

        $color= Color::create($request->all(['name']));
        return $color;
    }
}
