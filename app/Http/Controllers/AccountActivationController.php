<?php

namespace App\Http\Controllers;

use App\User;
use App\AccountActivation;
use Illuminate\Http\Request;

class AccountActivationController extends Controller
{

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $token='')
    {
        //
        $date = date('Y-m-d H:i:s');
        $getDetails = AccountActivation::where('token', $token)->first();
        if ($getDetails) {
            if ($date <= $getDetails->created_at) {
               $confirm = $getDetails->update(['status' => 1,]);
               if($confirm){
                   $user = User::where('id' ,$getDetails->user_id)->first();
                   $user->update(['email_verified_at' => $date]);
                   return response()->json(['status' => 'ok', 'message' => 'Email Verified Successfully', 'username'=>$user->username,'user_id'=>$user->id,'email'=>$user->email,'name'=>$user->name], 200);
               }
               else{
                return response()->json(['status' => 'not ok', 'message' => 'Email Can\'t be verified at this time. Try again'], 422);
               }
            }
            else{
                return response()->json(['status' => 'not ok', 'message' => 'Token Has Expired'], 401);
            }
        }
        else{
            return response()->json(['status' => 'not ok', 'message' => 'Invalid Token Provided'], 400);
        }
    }


}
