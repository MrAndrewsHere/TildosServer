<?php

namespace App\Http\Controllers;

use App\Project;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use DateTime;
use App\Page;

class PublicController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function get(Request $request){





        if(!$pr_id = Project::get_id($request->input('domain')))
        {
            return response()->json( [],404);
        }



        if($request->has("pageurl")){
            $project = Project::where('id',$pr_id)->first();

            $page='';
            if($pageurl = $request->input("pageurl") == ''){

                if($project->mainpage)
                {
                    $page = Page::getpublic($project->mainpage,null, null,$pr_id);
                    if(!$page)
                    {
                        return response()->json( [],404);
                    }

                }
                else
                {
                    return response()->json( [],404);
                }
            }
            else
            {
                $page = Page::getpublic(null, $request->input("pageurl"), null,$pr_id);
            }

            if($page){
                if($page->id !=$project->header && $page->id !=$project->footer)
                {
                    if($project->header )
                    {
                        $header = Page::getpublic($project->header,null, null,$pr_id);
                        if($header)
                        {
                            $page->data = json_encode(array_merge(json_decode($header->data),json_decode($page->data)));
                        }

                    }
                    if($project->footer )
                    {

                        $footer = Page::getpublic($project->footer,null, null,$pr_id);
                        if($footer)
                        {
                            $page->data = json_encode(array_merge(json_decode($page->data),json_decode($footer->data)));
                        }

                    }
                }

                return response()->json(["page" => $page,"project" =>$project], 200);
            } else {
                return response()->json( [],404);
            }
        }
        return response()->json( [],404);




    }

}
