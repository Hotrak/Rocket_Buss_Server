<?php

namespace App\Http\Controllers;

use App\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index(){
        return Settings::all();
    }

    public function update(Request $request){

        $settings = $request->settings;
        foreach ($settings as $item){
            DB::table('settings')
                ->where('id','=', $item['id'])
                ->update(['value' => $item['value']]);
        }
    }
}
