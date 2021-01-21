<?php

namespace App\Http\Controllers;

use App\Template;
use App\User;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Util\Json;

class TemplateController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

//        $this->middleware('auth');
//        $this->middleware('design');
    }

    public function getAll(){
           return response()->json(Template::allTMPLS(),200);
    }
    public function get($id){
        $res = Template::get($id);
        return response()->json($res?$res:'Не найден',$res?200:404);
    }
    public function create(Request $request){


        $user = auth()->user()?auth()->user()->id:'';
        $res = Template::create([
            'data' =>json_encode($request->json('data')),
            'user' => $user
        ]);
        return response()->json($res,200);
    }
    public function update($id,Request $request){


        if(!Template::get($id)){
            return response()->json('Шаблон не найден',404);
        };
        $data['data'] = json_encode($request->json('data'));
        $data['user'] = auth()->user()?auth()->user()->id:'';

        return response()->json(Template::updateTMPl($id,$data),200);
    }
    public function delete($id){
        $res =  Template::deleteTMPLS($id);
        return response()->json($res?$res:false,200);
    }

}
