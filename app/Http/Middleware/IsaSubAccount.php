<?php

namespace App\Http\Middleware;

use Closure;
use App\User;
use Auth;
class IsaSubAccount
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
        if(Auth::check()){
            $getUser = User::where('id',Auth::guard()->user()->id)->whereNull('seller_id')->first();
            if($getUser){
                return response()->json(['status'=>'not ok', 'message'=>'User did not have the right roles'],403);
            }
            else{
                return $next($request);
            }
        }
        else{
            return response()->json(['status'=>'not ok', 'message'=>'Unauthorised User'],401);
        }
        
    }
}
