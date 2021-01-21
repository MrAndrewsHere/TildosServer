<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use DateTime;

class StatsController extends Controller
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

    public function getStats(Request $request){
        $res = DB::select("select * from stats");
        return response()->json($res, 200);
    }

    public function changeStats(Request $request){
        $teachers = $request->input('teachers');
        $students = $request->input('students');
        $classes = $request->input('classes');


        DB::table("stats")->where("name", "teachers")->update(["data" => $teachers, "change_date" => date('Y-m-d H:i:s')]);
        DB::table("stats")->where("name", "students")->update(["data" => $students, "change_date" => date('Y-m-d H:i:s')]);
        DB::table("stats")->where("name", "classes")->update(["data" => $classes, "change_date" => date('Y-m-d H:i:s')]);

        return response(date('Y-m-d H:i:s'), 200);

    }
}
