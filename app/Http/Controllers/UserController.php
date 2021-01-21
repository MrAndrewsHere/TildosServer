<?php


namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use  App\User;


class UserController extends Controller
{
    /**
     * Instantiate a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Get the authenticated User.
     *
     * @return Response
     */


    public function check(){
        $user = Auth::user();
        $user['roles'] = User::find($user->id)->getRoleNames();
        return response()->json($user, 200);
    }
    public function allUsers()
    {
        return response()->json(['users' =>  User::all()], 200);
    }

    public function singleUser($id)
    {
        try {
            $user = User::findOrFail($id);

            return response()->json(['user' => $user], 200);

        } catch (\Exception $e) {

            return response()->json(['message' => 'user not found!'], 404);
        }

    }


}
