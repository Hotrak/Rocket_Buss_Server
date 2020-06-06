<?php

namespace App\Http\Controllers;

use App\LostThing;
use Illuminate\Http\Request;

class LostThingsController extends Controller
{
    public function index(){
        $news = LostThing::all();
        return response()->json(['lost_thing'=>$news],200);
    }

    public function store(Request $request){
        $path = $request->file('imgData')->store('uploads','public');
        $request['img'] = $path;
        $lostThing = LostThing::create($request->all());
        return $lostThing;
    }

    public function update(Request $request,$id){
        $new = LostThing::find($id);
//        Storage::delete('file.jpg');
        if($request['imageSet']=='true'){
//            return $request['imageSet'];
            $path = $request->file('imgData')->store('uploads','public');
            $new->img = $path;
        }
//        $request['img'] = $path;

        $new->name = $request['name'];


        $new->save();
        return $new;
    }

    public function destroy($id){
        $item = LostThing::find($id);
        $item->delete();
    }
}
