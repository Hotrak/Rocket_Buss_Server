<?php

namespace App\Http\Controllers;

use App\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $newsQuery = News::query();

        if($request->has('search'))
            foreach (['title','description'] as $item){
                $newsQuery->orWhere($item,'like','%'.$request->search.'%');
            }

        $news = $newsQuery->paginate($request->limit);
        return response()->json(['news'=>$news],200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $path = $request->file('imgData')->store('uploads','public');
        $request['img'] = $path;
        $new = News::create($request->all());
        return $new;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $new = News::find($id);
        return $new;
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $new = News::find($id);
//        Storage::delete('file.jpg');
        if($request['imageSet']=='true'){
//            return $request['imageSet'];
            $path = $request->file('imgData')->store('uploads','public');
            $new->img = $path;
        }
//        $request['img'] = $path;

        $new->title = $request['title'];
        $new->description = $request['description'];
        $new->body = $request['body'];

        $new->save();
        return $new;


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $new = News::find($id);
        $new->delete();
    }
}
