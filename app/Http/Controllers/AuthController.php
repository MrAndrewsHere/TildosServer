<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  App\User;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Store a new user.
     *
     * @param Request $request
     * @return Response
     */
    public function register(Request $request)
    {
        //validate incoming request
        $this->validate($request, [
            'name' => 'required|string',
            'login' => 'required|string|unique:users',
            'password' => 'required|min:8',
        ]);

        try {

            $user = new User();
            $user->name = $request->input('name');
            $user->login = $request->input('login');
            $plainPassword = $request->input('password');
            $user->password = app('hash')->make($plainPassword);

            $user->save();

            //return successful response
            return response()->json(['user' => $user, 'message' => 'CREATED'], 201);

        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'User Registration Failed!'], 409);
        }

    }

    /**
     * Get a JWT via given credentials.
     *
     * @param Request $request
     * @return Response
     */
    public function login(Request $request)
    {
        //validate incoming request
        $this->validate($request, [
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['login', 'password']);



        if (!$token = Auth::attempt($credentials)) {
            $ldap = $this->LDAP($credentials);
            if (isset($ldap['error']) || !$token = Auth::attempt($credentials)) {
                return response()->json(['message' => $ldap['error'] ? $ldap['error'] : 'Unauthorized'], 401);
            }
            $token = auth()->setTTL(172800)->attempt($credentials);
            return $this->respondWithToken($token,$user = User::where('login',$credentials['login'])->first());

        }
        $token=auth()->setTTL(172800)->attempt($credentials);
        return $this->respondWithToken($token,$user = User::where('login',$credentials['login'])->first());
    }


    public function LDAP($credentials)
    {

        $ldapserver = Env::get('LDAP_HOST');
        $ldapconn = ldap_connect($ldapserver);
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
        if ($ldapconn) {
            $login = $credentials['login'];
            $password = $credentials['password'];
            @$ldapbind = ldap_bind($ldapconn, 'unidomain\\' . $login, $password);
            if ($ldapbind) {


                $ldapbind = ldap_bind($ldapconn, Env::get('LDAP_USER'), Env::get('LDAP_PSWD'));
                if ($ldapbind) {
                    $result = ldap_search($ldapconn, "dc=unidomain, dc=uni-dubna, dc=ru", "(sAMAccountName=" . $login . ")", array("cn", "mail", "samaccountname"));
                    $data = ldap_get_entries($ldapconn, $result);
                    if ($data['count'] > 0) {

                        try {
                            $user = User::where('login', $login)->first();
                            if (!$user) {
                                $user = new User();
                            }

                            $user->name = $data[0]['cn'][0];;
                            $user->login = $login;
                            $plainPassword = $password;
                            $user->password = app('hash')->make($plainPassword);

                            $user->save();
                            ldap_close($ldapconn);

                            return $credentials;

                        } catch (\Exception $e) {
                            $error = 'Не удалось создать пользователя';
                        }

                    } else {
                        $error = 'Не удалось найти пользователя';
                    }
                } else {
                    $error = 'Не удалось подключиться к серверу авторизации. Попробуйте позже';
                }

            } else {
                $error = 'Access denied';
            }


        } else {
            $error = 'Доменная авторизация не доступна. Попробуйте позже';
        }

        ldap_close($ldapconn);
        return ['error' => $error];


    }

}
