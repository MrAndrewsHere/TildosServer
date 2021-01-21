<?php

namespace App\Http\Controllers;

use App\DCR;
use App\Project;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use DateTime;
use App\Page;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class DomainRegistrationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       $this->middleware('role:domain-registration|super-admin');
    }

    public function all()
    {
        return response()->json(DCR::with('requestUser', 'responseUser')->get(), 200);
    }

    public function DCRrequest(Request $request)
    {

        $this->validate($request, [
            'id' => 'required | integer | exists:projects',
            'new_domain_name' => 'required | string| min:3'
        ]);

        $data = [
            'project_id' => $request->input('id'),
            'domain' => $request->input('new_domain_name'),
        ];
        return response()->json(DCR::requestConnectionDomain($data), 200);
    }

    public function DCRclose(Request $request)
    {

        $data = [
            'dcrID' => $request->input('dcrID'),
            'success' => $request->input('success') ?? 1,
            'comment' => $request->input('comment') ?? null
        ];
        $result = DCR::close($data);

        return response()->json($result, 200);
    }


}
