<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use DateTime;

class FAQController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getFAQ(Request $request){
        $res = DB::select("select * from faq order by id desc");
        return response()->json([
            "FAQ" => $res
        ], 200);
    }

    public function addFAQ(Request $request){
        $person_type = $request->input("person_type");
        $question = $request->input("question");
        $answer = $request->input("answer");

        $gettedId = DB::table("faq")->insertGetId([
            "person_type" => $person_type,
            "question" => $question,
            "answer" => $answer,
        ]);

        return response()->json([
            "id" => $gettedId
        ], 200);
    }

    public function deleteFAQ($id){
        DB::table("faq")->where("id", $id)->delete();
    }

}
