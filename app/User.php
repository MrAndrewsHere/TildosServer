<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
class User extends Model implements AuthenticatableContract, AuthorizableContract,JWTSubject
{
    use Authenticatable, Authorizable,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'login',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];



    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function projects(){
        return $this->belongsToMany('App\Project', 'project_user','user_id','project_id');
    }


//    static public function getProjects($user_id){
//
//        $sql = "select p.id as project_id,
//                p.name as project_name,
//                p.domain,p.techdomain,
//                kk.user_name as user_name,
//                kk.login,
//                r.name as role
//                FROM  (SELECT u.id as user_id,
//                 u.name as user_name,
//                 u.login,
//                 pu.project_id as project_id,
//                 pu.role_id as role_id
//                 from users u
//                 JOIN project_user pu
//                 on  u.id=pu.user_id where u.id = ? ) kk
//                left JOIN projects p
//
//                on kk.project_id = p.id
//                left join roles r
//                on kk.role_id=r.id";
//        return DB::select($sql,[$user_id]);
//    }

    static public function get_projet($user_id,$project_id){
       // $sql = "SELECT pg.id as page_id, pg.title, pg.url, pg.is_public from pages pg right JOIN (select p.id as project_id ,p.name as project_name,p.domain,p.techdomain, r.name as role FROM projects p left JOIN (SELECT u.id as user_id, pu.project_id as project_id, pu.role_id as role_id from users u JOIN project_user pu on  u.id=pu.user_id where u.id = ? and pu.project_id = ? ) kk on p.id = kk.project_id left join roles r on kk.role_id=r.id) tmp on pg.project_id = tmp.project_id";
       $sql = "select id,title,url,is_public,changed,tmpl from pages where project_id=?";


            $pages =  DB::select($sql,[$project_id]);
            $project = DB::select('select * from projects where id=?',[$project_id])[0];
            return ['project'=>$project,'pages'=>$pages];

    }
    function hasProject($projectID){
        return true;
    }
    function hasRoleOnProject($projectID,$roleID){
        return true;
    }


}
