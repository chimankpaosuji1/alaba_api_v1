<?php

namespace App\Http\Controllers;

use App\Login;
use App\Islocked;
use App\AccountActivation;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class IslockedController extends Controller
{
    //

    public function islocked(){
        $lock = Login::select('user_id', $user_id)->count(5);
        $reason = "Someone is trying to access your account";
        if($lock){
            $data['user_id'] = Auth::guard()->user()->id;
            $data['reason'] = $reason;
            $log = new Islocked;
            $save = $log->save();
            return response()->json(['status'=>'ok', 'messsage'=>'saved successfully']);
        }


        
    }





}
