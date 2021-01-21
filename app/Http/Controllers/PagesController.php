<?php

namespace App\Http\Controllers;

use App\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use DateTime;
use App\Page;

class PagesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('domain_auth');
        $this->middleware('cors');

    }

    public function delete(Request $request){

        $pr_id = $request->has('project_id')? $request->input('project_id'):Project::get_id( $request->input('domain'));
        $id = $request->input('id');

        if($id && $pr_id){


            if(Page::deletePage($id,$pr_id)){
                return response()->json(true, 200);
            } else {
                return response()->json(["error" => "Нет такой страницы"], 404);
            }
        }
        return response()->json(["error" => "Нет такой страницы"], 404);
    }

    public function duplicate(Request $request){
        $pr_id = $request->has('project_id')? $request->input('project_id'):Project::get_id( $request->input('domain'));
        $id = $request->input('id');

        if($id && $pr_id){

            $page = Page::get($id, null, null,$pr_id);
            if($page){


                $copy = [
                    "data" => $page->data,
                    "title" =>   'Copy of '.$page->title,
                    "url" =>     substr(md5($page->url . date('YmdHis')), 0, 5).'-'.$page->url,
                    "tmpl" => 0,
                    "project_id" => $pr_id,
                    "is_public" => 0,
                ];

                $id = Page::create($copy);
                return response()->json(["id" => $id], 200);
            } else {
                return response()->json(["error" => "Нет такой страницы"], 404);
            }
        }
        return response()->json(["error" => "Нет такой страницы"], 404);
    }

    public function publish(Request $request){
        $pr_id = $request->has('project_id')? $request->input('project_id'):Project::get_id( $request->input('domain'));

        $pr_id = intval($pr_id);
        if($request->has('pageid'))
        {
            $id = $request->input('pageid');
            Page::publish($id,$pr_id);
            return response()->json(true, 200);
        }
        if($request->has('all'))
        {
            $pages = Project::get_pages($pr_id,false);
            foreach ($pages as $page)
            {
                Page::publish($page->id,$pr_id);
            }
            return response()->json(true, 200);

        }
        return response()->json(false, 500);

    }


    public function create(Request $request){

        $this->validate($request, [
            'title' => 'required|string',
            'url' => 'required|string',


        ]);

        $pr_id = $request->has('project_id')? $request->input('project_id'):Project::get_id( $request->input('domain'));

        if(Page::get(null, $request->input("url"), null,$pr_id)){
            return response()->json(["error" => 'Адрес занят'], 409);
        }

        $id = Page::create([
            "data" => '[]',
            "title" => $request->input("title"),
            "url" => $request->input("url"),
            "tmpl" => $request->input("tmpl")||0,
            "project_id" =>$pr_id,
            "is_public" =>$request->input("isPublic")||0,
        ]);
        return response()->json(["page" => ["id" => $id]], 200);
    }

    public function save(Request $request, $id){

        $pr_id = $request->has('project_id')? $request->input('project_id'):Project::get_id( $request->input('domain'));

//
//        if($request->has(   "blocks")){
//            $data["data"] = $request->input("blocks");
//
//            $pag = Page::get($id,null,null,$pr_id);
//            $pagdata=json_decode($pag->data);
//            $data["data"] = (array_replace_recursive($pagdata,json_decode($data["data"])));
//
//        }
        $data=[];

        if($request->has(   "blocks")){
            $data["data"] = $request->input("blocks");
        }

        $data["changed"] =1;

        Page::updatee($id,$pr_id, $data);
        return response()->json(["id" => $id], 200);
    }

    public function update(Request $request, $id){

        $pr_id = $request->has('project_id')? $request->input('project_id'):Project::get_id( $request->input('domain'));


        $data=[];
        if($request->has("title")){
            $data["title"] = $request->input("title");
        }
        if($request->has("url")){
            if($tm = Page::get(null, $request->input("url"), null,$pr_id)){
                if($tm->id != $id)
                {
                    return response()->json(["error" => 'Адрес занят'], 409);
                }
                $data["url"] = $request->input("url");
            }
            $data["url"] = $request->input("url");
        }
        if($request->has(   "blocks")){
            $data["data"] = $request->input("blocks");
        }
        if($request->has("tmpl")){
            $data["tmpl"] = $request->input("tmpl");
        }
        $data["changed"] =1;

        Page::updatee($id,$pr_id, $data);
        return response()->json(["id" => $id], 200);
    }

    public function get(Request $request, $id = null){




        $pr_id = $request->has('project_id')? $request->input('project_id'):Project::get_id( $request->input('domain'));

        $limit = null;

        if($request->has("pageurl")){
            $page = Page::get(null, $request->input("pageurl"), null,$pr_id);
            if($page){
                return response()->json(["page" => $page], 200);
            } else {
                return response()->json(["error" => "Нет такой страницы"], 404);
            }
        }

        if($id){

            $page = Page::get($id, null, null,$pr_id);
            if($page){
                return response()->json(["page" => $page], 200);
            } else {
                return response()->json(["error" => "Нет такой страницы"], 404);
            }
        }

        if ($request->has("page")){
            $page = $request->input('page');
            $itemsPerPage = $request->input('itemsPerPage');
            $from = $itemsPerPage * ($page - 1);
            $limit = [
                "from" => $from,
                "count" => $itemsPerPage
            ];
        }

        $res = Page::get(null, null, $limit,$pr_id);
        if($res){
            return response()->json([
                "pages" => $res,
                "total" => Page::totalCount($pr_id)
            ], 200);
        } else {
            return response()->json([
                "pages" => null,
                "total" => Page::totalCount($pr_id)
            ], 404);
        }

    }

}
