<?php

namespace App\Http\Middleware;

use Closure;
use App\Wallet;
//use Illuminate\Support\Facades\Hash;
use Auth;

class CheckWalletLoginStatus
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
            $current_time = date('Y-m-d H:i:s');
            $user = Auth::guard()->user()->id;
            $getDetails = Wallet::where('user_id', $user)->first();
            if(!$request->headers->all()['wallet']){
                return response()->json(['status'=> 'Not ok', 'message'=> 'Unauthorized User'], 403);
            }
            if($getDetails->token == $request->headers->all()['wallet'][0]){
                if($current_time > $getDetails->expired_at){
                    Wallet::where('user_id', $user)->update(['status'=> 0, 'token'=> NULL]);
                    return response()->json(['status'=> 'Not ok','message'=>'User Session has expired please login again'],403);
                }
            }
            else{
                return response()->json(['status' => 'not ok', 'message' => 'Token not found'], 404);
            }
            return $next($request);
        }
        else{
            return response()->json(['status' => 'not ok', 'message' => 'Unauthorised User'], 401);
        }

    }
}
