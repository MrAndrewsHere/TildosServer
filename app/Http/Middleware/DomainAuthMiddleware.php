<?php

namespace App\Http\Middleware;

use App\Project;
use Closure;
use Illuminate\Http\Request;
class DomainAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {


      if($request->has('domain') && !$pr_id = Project::isset('',$request->input('domain')))
      {
          return response()->json(['error'=>'1'],404);
      }
        if($request->has('project_id') && !$pr_id = Project::isset($request->input('project_id'),''))
        {
            return response()->json(['error'=>'2'],404);
        }



        return $next($request);
    }
}
