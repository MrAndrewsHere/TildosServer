<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use DB;

class Page extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    static function getpublic($id, $url, $limit,$project_id){
       return self::get($id, $url, $limit,$project_id,'saveddate');
    }
    static function get($id, $url, $limit,$project_id,$datatype = 'data'){
        $data = [];
        $query = "select id, title, url, $datatype as data, is_public, tmpl from pages where ";

        if($id){
            $query .= "id = $id ";
            $query .= "and project_id = $project_id";
            $res = DB::select($query);
            if(count($res) > 0){
                return $res[0];
            } else {
                return null;
            }
        } else {
            $query .= "id = id ";

        }

        if($url){

            $query .= "and url = ? ";
            $query .= "and project_id = ?";


            $res = DB::select($query,[$url,$project_id]);

            if(count($res) > 0){
                return $res[0];
            } else {
                return null;
            }
        }



        $query .= "order by id desc ";

        if($limit){
            array_push($data, $url);

            $from = $limit["from"];
            array_push($data, $url);
            $count = $limit["count"];
            $query .= "limit $from, $count ";
        }




        $res = DB::select($query);
        if(count($res) > 0){
            return $res;
        } else {
            return null;
        }
    }

    public static function totalCount($project_id){
        $res = DB::select("select count(id) as count from pages where project_id=?",[$project_id]);
        return $res[0]->count;
    }

    static function publish($id,$project_id){
       $page =  self::get($id,'','',$project_id);


        $data = [
            'saveddate' =>$page->data,
            'is_public' =>1,
            'changed' =>0,
        ];
        self::updatee($id,$project_id,$data);


    }

    static function updatee($id,$project_id, $data){
        DB::table("pages")->where([["id", $id],['project_id',$project_id]])->update($data);
    }

    static function create($data){
        $id = DB::table("pages")->insertGetId($data);
        return $id;
    }
    static function deletePage($id,$project_id){
      return   DB::table("pages")->where([["id", $id],['project_id',$project_id]])->delete();
    }


}
