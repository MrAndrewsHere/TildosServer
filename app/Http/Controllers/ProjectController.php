<?php


namespace App\Http\Controllers;

use App\Project;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use Illuminate\Support\Env;
use Mockery\Exception;

class ProjectController extends Controller
{
    function __construct()
    {
        $this->middleware('auth');
        $this->middleware('domain_auth');
    }

    public function get_user_projects()
    {
        return response()->json(['projects' => User::find(auth()->user()->id)->projects()->get()], 200);
    }


    public function get_project($id){
        if(User::find(auth()->user()->id)->hasRole('super-admin') || Project::project_guard(auth()->user()->id,$id))
        {
            return response()->json(Project::get_project($id),200);
        }

        return response()->json(['error'=>'Not found'], 404);
    }

    public function searchcollaborators(Request $request){
     $query = $request->json('query');
     $like = "'%".$query."%'";
     $find = \Illuminate\Support\Facades\DB::select("select name as user_name, login from users where login LIKE ? and login !=?",[$like,auth()->user()->login]);

        if(iconv_strlen($query) >2)
        {
            return response()->json(['result' => array_merge($find,$this->LDAP($query)) ], 200);
        }
        return response()->json(['result' =>$query], 200);

    }

    public function LDAP($credentials)
    {

        $ldapserver = Env::get('LDAP_HOST');
        $ldapconn = ldap_connect($ldapserver);
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
        if ($ldapconn) {
            $ldapbind = ldap_bind($ldapconn, Env::get('LDAP_USER'), Env::get('LDAP_PSWD'));
            if ($ldapbind) {
                $result = ldap_search($ldapconn, "dc=unidomain, dc=uni-dubna, dc=ru", "(sAMAccountName=*" . $credentials . "*)", array("cn", "mail", "samaccountname"));
                $data = ldap_get_entries($ldapconn, $result);
                if ($data['count'] > 0) {
                    $res = [];
                    foreach ($data as $us)
                    {
                        if(is_array($us) &&  auth()->user()->login !== $us['samaccountname'][0] )
                        {
                            $res[]= ['user_name' => $us['cn'][0],'login'=>$us['samaccountname'][0]];
                        }

                    }

                   return $res;

                } else {
                    $error = 'Не удалось найти пользователя';
                }
            } else {
                $error = 'Не удалось подключиться к серверу авторизации. Попробуйте позже';
            }

        } else {
            $error = 'Доменная авторизация не доступна. Попробуйте позже';
        }

        ldap_close($ldapconn);
        return ['error' => $error];


    }
    public function projectsettings($id){

        try {
            if($result = User::get_projet(auth()->user()->id,$id))
            {

                $result['collaborators'] = Project::get_collaborators($id);
                $result['roles'] = \Illuminate\Support\Facades\DB::select("select id,name from roles where name='adminP' or name='editorP' or name='ownerP' ");
                return response()->json($result, 200);
            }
        }
        catch (Exception $exception)
        {
            return response()->json($exception, 404) ;
        }

    }

    public function create_project(Request $request){
        $this->validate($request, [
            'name' => 'required|string|min:3',
        ]);

        if($result = Project::create($request->input('name')))
        {
            return response()->json(['project_id' => $result], 200);
        }
         return response()->json(false, 500);

    }

    public function update_project($id,Request $request){

         $pr_id = $id;
         $main = $request->has('main')?$request->input('main'):null;
         $collaborators = $request->has('collaborators')?$request->input('collaborators'):null;

         if($main)
         {
             Project::update_project($pr_id,$main);
         }


        $userinpr = \Illuminate\Support\Facades\DB::select("select * from project_user where project_id=? and role_id!=(select id from roles where name ='owner')",[$pr_id]);

        if($collaborators)
        {

            foreach ($collaborators as $collab)
            {
                if(!$user = User::where('login',$collab['login'])->first())
                {
                    $user = new User();
                    $user->login = $collab['login'];
                    $user->name = $collab['user_name'];
                    $user->save();
                }
                $user_id = $user->id;
                $userinpr = array_filter($userinpr, function ($value) use ($user_id){
                    return $value->user_id !== $user_id;
                });




                Project::add_user_to_project($collab['role_id'],$user_id,$pr_id);
            }



        }
        foreach ($userinpr as $user)
        {
            \Illuminate\Support\Facades\DB::select('delete from project_user where user_id=? and project_id=?',[$user->user_id,$pr_id]);
        }
        return response()->json(true, 200);



    }
}
