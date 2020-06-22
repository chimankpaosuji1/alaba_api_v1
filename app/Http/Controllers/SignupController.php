<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\AccountActivation;
use App\Events\NewCustomerHasRegisteredEvent;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class SignupController extends Controller
{
    public function sendmail()
     {
         $data = [

             'title' => 'Hello World',
             'content' => 'Good'
         ];

         Mail::send('emails.test', $data, function ($message) {

         $message->from('noreply@alabamarket.com')->to('jimohopeoluwa@gmail.com', 'Opeoluwa')->subject('hello sudent');
         });
    }
    public function resendmail(Request $request)
    {
        $rules = [
            'name' => 'required|min:3',
            'email' => 'required|min:3',
            'user_id' => 'required',
        ];

       $validator = Validator::make($request->all(), $rules);
       if ($validator->fails()) {
           return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
       }
       else {
           $data = $request->only(['name', 'user_id', 'email']);
           $user = new User($data);
           $getExist = AccountActivation::where('user_id', $request->user_id)->first();
           if ($getExist) {
            AccountActivation::where('user_id', $request->user_id)->delete();
            $user['token'] = $token = md5(strtolower($request->email) . mt_rand(13, 23) . strtotime(\Carbon\Carbon::now(1)));
            $acctivation = new AccountActivation();
            $acctivation->token = $token;
            $acctivation->status=0;
            $acctivation->user_id = $request->user_id;
            $acctivation->created_at = date("Y-m-d H:i:s", strtotime('+24 hours'));
            $acctivation->save();
            $confirm = event(new NewCustomerHasRegisteredEvent($user));
            if ($confirm) {
                return response()->json(['status' => 'ok', 'message'=>'Email Sent Successfully! Check your Mail'], 201);
            }
            else{
                return response()->json(['status' => 'not ok', 'message' => 'Error Sending Mail! Try again '], 422);
            }
           }
            $user['token'] = $token = md5(strtolower($request->email) . mt_rand(13, 23) . strtotime(\Carbon\Carbon::now(1)));
            $acctivation = new AccountActivation();
            $acctivation->token = $token;
            $acctivation->status=0;
            $acctivation->user_id = $request->user_id;
            $acctivation->created_at = date("Y-m-d H:i:s", strtotime('+24 hours'));
            $acctivation->save();
            $confirm = event(new NewCustomerHasRegisteredEvent($user));
            if ($confirm) {
                return response()->json(['status' => 'ok', 'message'=>'Email Sent Successfully! Check your Mail'], 201);
            }
            else{
                return response()->json(['status' => 'not ok', 'message' => 'Error Sending Mail! Try again '], 422);
            }
       }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        $rules = [
            'name' => 'required|min:3',
            'username' => 'required|min:3|unique:users',
            ($request->email)?'email':'phone' => 'required|min:3|unique:users',
            'password' => 'required|min:3|confirmed',
        ];

       $validator = Validator::make($request->all(), $rules);
       if ($validator->fails()) {
           return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
       }

        else {
            # code...
            ($request->email)?$params = 'email': $params = 'phone';
            $data = $request->only(['username', $params, 'password', 'name']);
            $data['password'] = Hash::make($request->password);

            $user = new User($data);
            $confirm = $user->save();

            if ($user->email) {
                $user['token'] = $token = md5(strtolower($user->email) . mt_rand(13, 23) . strtotime(\Carbon\Carbon::now(1)));
                    $acctivation = new AccountActivation();
                    $acctivation->token = $token;
                    $acctivation->status=0;
                    $acctivation->user_id = $user->id;
                    $acctivation->created_at = date("Y-m-d H:i:s", strtotime('+24 hours'));
                    $acctivation->save();
                    event(new NewCustomerHasRegisteredEvent($user));
            }
            if ($confirm) {
                return response()->json(['status' => 'ok', 'message'=>'User Successfully registered'], 201);
            }
            else{
                return response()->json(['status' => 'not ok', 'message' => 'Error registering User, Check form and submit again '], 422);
            }
        }



    }

   
}
