<?php

namespace App\Http\Controllers;

use App\Project;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use DateTime;
use App\Page;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('role:super-admin');
    }

    public function getAllProjects()
    {
        $projects = Project::with('users')->get();

        foreach ($projects as &$project) {
            foreach ($project['users'] as &$user) {
                $user['role'] = Role::findById($user->pivot->role_id);
            }
        }
        return response()->json($projects, 200);
    }

    public function getProject($id)
    {
        return response()->json(Project::get_project($id), 200);
    }

    public function test(Request $request)
    {
        return response()->json(['projects' => User::find(auth()->user()->id)->projects()->get()], 200);
    }

    public function setRole(Request $request)
    {
        $this->validate($request, [
            'userID' => 'required|integer',
            'roleID' => 'required|integer',
        ]);

        if (!$user = User::find($request->input('userID')) || !$role = Role::findById($request->input('roleID'))) {
            return response()->json(['NotFound'], 404);
        }
        $user->assignRole($role);
        return response()->json(true, 200);
    }

}
