<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use DateTime;

class ReportsController extends Controller
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

    public function getReports(Request $request){
        $res = DB::select("select * from reports order by date desc");
        foreach ($res as $item){
            $item->date = date("d.m.Y", strtotime($item->date));
        }
        return response()->json([
            "reports" => $res
        ], 200);
    }

    public function addReport(Request $request){
        $date = $request->input("date");
        $url = $request->input("url");

        $gettedId = DB::table("reports")->insertGetId([
            "date" => $date,
            "url" => $url
        ]);

        return response()->json([
            "id" => $gettedId
        ], 200);
    }

    public function deleteReport($id){
        DB::table("reports")->where("id", $id)->delete();
    }

}
