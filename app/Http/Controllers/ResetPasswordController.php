<?php

namespace App\Http\Controllers;

use App\Islocked;
use App\User;
use Validator;
use App\PasswordResets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Events\SendPasswordResetLinkEvent;
class ResetPasswordController extends Controller
{


    public function sendmail(Request $request)
    {
        $user = User::where('email', '=', $request->email)->first();

        if (!$user) {
            return response()->json(['status' => 'not ok', 'message' => 'User Not Found'], 400);
        }

            $reset_token = $user->username . strtotime(\Carbon\Carbon::now(1));
            $data['token'] = md5($reset_token);
            $data['email'] = $request->email;
            $data['reason'] = 'passwordreset';
            $passwordReset = new PasswordResets($data);
            $passwordReset->created_at = date("Y-m-d H:i:s", strtotime('+24 hours'));
            $confirm = $passwordReset->save();
            $passwordReset['user_name'] = $user->username;
            event(new SendPasswordResetLinkEvent($passwordReset));

            return response()->json(['status' => 'ok', 'message' => 'Password Reset Link Sent Successfully'], 200);
    }

    public function confirmreset(Request $request, $token='')
    {
        $date = date('Y-m-d H:i:s');
        $getDetails = PasswordResets::where('token', $token)->first();
        if ($getDetails) {
            $getUser = User::where('email', $getDetails->email)->first();
            if($getDetails->reason == 'account locked'){
               Islocked::where('user_id',$getUser->id)->update(['status'=>0]);
            }
            if ($date <= $getDetails->created_at) {

                $rules = [ 'password' => 'required|min:3|confirmed'];

                $validator = Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
                }
                else{
                    $newpassword = Hash::make($request->password);
                    $confirm = $getUser->update(['password' => $newpassword]);
                    if ($confirm) {
                        PasswordResets::where('token', $token)->delete();
                        return response()->json(['status' => 'ok', 'message' => 'Password Changed Successfully'], 200);
                    }
                    else{
                     return response()->json(['status' => 'not ok', 'message' => 'Password Can\'t be changed at this time. Try again'], 422);
                    }
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
