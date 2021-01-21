<?php
/**
 * Created by PhpStorm.
 * User: ed
 * Date: 12.03.19
 * Time: 11:54
 */

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{

    protected $table = 'templates';
    protected $fillable = ['user','data'];

    static function allTMPLS(){
        $sql = 'select * from templates ';
        $res = \Illuminate\Support\Facades\DB::select($sql);
        return $res;
    }

    static function get($id){

        $sql = 'select * from templates where id = ?';
        $res = \Illuminate\Support\Facades\DB::select($sql,[$id]);
        return $res?$res:null;
    }
    static function create($data){



        $id = \Illuminate\Support\Facades\DB::table("templates")->insertGetId($data);
        $sql = 'select * from templates where id = ?';
        $res = \Illuminate\Support\Facades\DB::select($sql,[$id]);
        return $res;
    }
    static function updateTMPl($id,$data){
         \Illuminate\Support\Facades\DB::table("templates")->where("id", $id)->update($data);
        $sql = 'select * from templates where id = ?';
        $res = \Illuminate\Support\Facades\DB::select($sql,[$id]);
        return $res;
    }
    static function deleteTMPLS($id){
        return \Illuminate\Support\Facades\DB::table("templates")->delete($id);
    }


}
