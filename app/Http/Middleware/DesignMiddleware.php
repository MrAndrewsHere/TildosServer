<?php

namespace App\Http\Middleware;

use Closure;

class DesignMiddleware
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
        //changes=Roles
        $whiteList = ['dolzhenko.a.n',' teplyashina.d.s',' sadyrova.t.i','mikheev.m.a','emelkina.a.a'];
        if(in_array(auth()->user()->login,$whiteList))
        {
            return $next($request);
        }
       return  response()->json('Access denied',200);
    }
}
