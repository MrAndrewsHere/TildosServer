<?php


namespace App;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use Spatie\Permission\Models\Role;
class Project extends Model
{

    protected $table = "projects";
    public $timestamps = false;
    public function users(){
        return $this->belongsToMany('App\User', 'project_user', 'project_id', 'user_id')->withPivot('role_id');
    }
//    static function getAllProjects(){
//        $projects = DB::select('select * from projects');
//        foreach ($projects as &$project)
//        {
//            $project['users'] = DB::select('select * from users where id in (select user_id from project_user where project_id = ?)',[$project['id']]);
//        }
//        return $projects;
//    }
    static function create($name)
    {

        try {
            $project_id = DB::table("projects")->insertGetId(['name' => $name]);
            $roleID = Role::findByName('ownerP')->id;
            DB::table("projects")->where("id", $project_id)->update(['techdomain' => 'project' . $project_id]);
            DB::table("project_user")->insert(['project_id' => $project_id, 'role_id' => $roleID, 'user_id' => auth()->user()->id]);
            return $project_id;
        } catch (Exception $exception) {
            return false;
        }


    }

    static function get_collaborators($id)
    {

        //  and pu.role_id != (SELECT id FROM roles where name='owner')
        $sql = "SELECT
                        t.name as user_name,
                        t.login,
                        r.name as role_name,
                        r.id as role_id
                        from (
                            SELECT
                            pu.user_id,
                            pu.role_id,
                            u.id,
                            u.name,
                            u.login
                            from project_user pu
                            LEFT JOIN users u
                            on pu.user_id = u.id
                            where pu.project_id = ?
                        )
                        t LEFT JOIN roles r
                        on r.id = t.role_id";
        if ($result = DB::select($sql, [$id])) {
            return $result;
        }
        return [];
    }

    static function set_role($role_id, $user_id, $project_id)
    {
        $sql = 'UPDATE
                        project_user
                        set role_id=?
                        where user_id=? and project_id=?';
        if (DB::select($sql, [$role_id, $user_id, $project_id])) {
            return true;
        }
        return false;

    }

    static function add_user_to_project($role_id, $user_id, $project_id)
    {

        $sql = "select * from roles where id=?";
        if ($res = DB::select($sql, [$role_id])) {
            if ($res[0]->name === 'ownerP') return false;

        } else {
            return false;
        }

        $sql = "select user_id from project_user where  user_id=? and project_id=? ";
        if (DB::select($sql, [$user_id, $project_id])) {
            return self::set_role($role_id, $user_id, $project_id);
        }
        $sql = "insert into
                        project_user(USER_ID,project_ID,role_ID)
                        VALUES (?,?,?)";
        if (DB::select($sql, [$user_id, $project_id, $role_id])) {
            return true;
        }
        return false;

    }

    static public function get_project($project_id){

        $sql = "select id,title,url,is_public,changed,tmpl from pages where project_id=?";
        $pages =  DB::select($sql,[$project_id]);
        $project = DB::select('select * from projects where id=?',[$project_id])[0];
        $dcr = DCR::with('requestUser')->where([
            ['project_id','=',$project_id],
            ['closed','=',0]
        ])->first();
        return ['project'=>$project,'pages'=>$pages,'dcr' =>$dcr];

    }
    static function get_pages($project_id, $tmpl)
    {
        $sql = "select id,title,url,is_public,changed,tmpl from pages where project_id=? ";
        if ($tmpl) {
            $sql .= " and tmpl=?";
            return DB::select($sql, [$project_id, $tmpl]);
        }


        return DB::select($sql, [$project_id]);
    }

    static function get_id($domain)
    {
        if ($result = DB::select('select id from projects where domain=? or techdomain=?', [$domain, $domain])) {
            return $result[0]->id;
        }
        return false;
    }

    static function isset($id = null, $domain = null)
    {
        if ($id) {
            if (DB::select('select id from projects where id=?', [$id])) {
                return $id;
            }
            return false;
        }
        if ($domain) {
            return self::get_id($domain);
        }
        return false;

    }

    static function project_guard($user_id, $project_id)
    {
        if (DB::select('select project_id from project_user where user_id=? and project_id=?', [$user_id, $project_id])) {
            return true;
        }
        return false;
    }

    static function update_project($id, $data)
    {
        return DB::table("projects")->where([["id", $id]])->update($data);
    }
    function hasUser($userID){
        return true;
    }
    function hasUserWithRole($userID,$roleID){
        return true;
    }
}
