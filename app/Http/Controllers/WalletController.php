<?php

namespace App\Http\Controllers;

use App\User;
use Validator;
use App\Wallet;

use App\Transaction;
use Tymon\JWTAuth\JWTAuth;
use App\TransactionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WalletController extends Controller
{

    public function wallauth(Request $request){
        if(Auth::check()){
            $user = Auth::guard()->user()->id;
            $token = $this->gentoken();
            $expired_at = $date = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            if(!Hash::check($request->password, Wallet::where('user_id', $user)->first()->password)){
                return response()->json(['status'=>'not ok','message'=>'Incorrect Pin'],406);
            }else{
                Wallet::where('user_id', $user)->update(['token'=> $token, 'expired_at'=> $expired_at, 'status'=> 1 ]);
                return response()->json(['message' => $token],201 );
            }


        }
        else{
            return response()->json(['message' => 'Unauthorised'],401);
        }



    }


    public function gentoken(){
             //Generate a random string.
            $token = openssl_random_pseudo_bytes(100);
            //Convert the binary data into hexadecimal representation.
             $token = bin2hex($token);
            //Print it out for example purposes.
            return ($token);
    }

    public function getWalletBalance()
    {
        $user = Auth::guard()->user()->id;
        $wallet = Wallet::where('user_id', $user)->first()->wallet_balance;
        return response()->json(['balance' => $wallet], 200);
    }

    public function fundUserWallet(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'amount' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
        }
        $data = $request->only(['user_id', 'amount']);
        $getUser = User::where('id',$request->user_id)->first();
        if ($getUser){
            $getUserBalance = Wallet::where('user_id',$request->user_id)->first();
            $confirm = $getUserBalance->update(['wallet_balance' => $getUserBalance->wallet_balance + $request->amount]);
            if ($confirm){
                $data['user_id'] = $request->user_id;
                $data['type'] = 'wallet';
                $data['amount'] = $request->amount;
                $data['description'] = $request->amount.' Was Added to your Wallet by '.Auth::guard()->user()->username;
                $history = new TransactionHistory($data);
                $history->save();
                return response()->json(['message' => 'User Credited Successfully'], 200);
            }
        }
        else{
            return response()->json(['message' => 'User Not Found'], 404);
        }
    }

    public function userFundWallet(Request $request)
    {
        $rules = [
            'amount' => 'required'
        ];


        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
        }

        $data = $request->only(['amount']);

        $getUser = User::where('id',Auth::guard()->user()->id)->first();
        if ($getUser){
            $data['email'] = $getUser->email;
            $data['amount'] = $request->amount;
            if($link = $this->process($data)){
//               $getUserBalance = Wallet::where('user_id',Auth::guard()->user()->id)->first();
//            $confirm = $getUserBalance->update(['wallet_balance' => $getUserBalance->wallet_balance + $request->amount]);
//            if ($confirm){
                return response()->json(['message' => $link],200);
//            }
            }

        }
        else{
            return response()->json(['message' => 'User Not Found'], 404);
        }
    }

    public function process($details) {
        if (isset($details)) {
                $curl = curl_init();
                $data['email'] = $email = $details['email'];
                $amount = $details['amount'] * 100; //the amount in kobo. This value is actually NGN 300
                $data['trans_id'] = uniqid();
                curl_setopt_array($curl, array(CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
                    CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_POSTFIELDS =>
                        json_encode(['amount' => $amount, 'email' => $email ]),
                    CURLOPT_HTTPHEADER => ["authorization: Bearer sk_live_5bc039529addf9f4d6a6890df53c9d6ee4517089", //replace this with your own test key
                    "content-type: application/json", "cache-control: no-cache"],));
                $response = curl_exec($curl);
                $err = curl_error($curl);
                if ($err) {
                    // there was an error contacting the Paystack API
                    return response()->json(['status' => 'not ok', 'message' => $err], 400);
                }
                $tranx = json_decode($response, true);
                if (!$tranx['status']) {
                    // there was an error from the API
                    return response()->json(['status' => 'not ok', 'message' => 'API returned error: ' . $tranx['message']], 400);
                }
                // comment out this line if you want to redirect the user to the payment page
                $data['access_code'] = $tranx['data']['access_code'];
                $data['reference'] = $tranx['data']['reference'];
                $data['amount'] = $amount / 100;
                $data['user_id'] = Auth::guard()->user()->id;
                $data['username'] = Auth::guard()->user()->username;
                $data['email'] = Auth::guard()->user()->email;
                $data['pay_type'] = 'wallet';

                $transaction = new Transaction($data);
                $confirm = $transaction->save();
                if (!$confirm){
                    return response()->json(['status' => 'not ok', 'message' => 'Unable to Register your payment Info!!! Check Form and Try Again'], 400);
                }
                return $tranx['data'];
        }
    }


    public function changewalletpassword(Request $request){
        $user = Auth::guard()->user()->id;

        $rules = [
                    'new_password' => 'required',
                    'new_confirm_password' => 'same:new_password',
                 ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
    }
    // dd(Hash::make($request->password));
    if(!Hash::check($request->password, Wallet::where('user_id', $user)->first()->password)){
       return response()->json(['status'=>'not ok','message'=>'Old Password didn\'t match'],406);
    }
    $userId = Auth::guard()->user()->id;
    if($userId){
        Wallet::where('user_id', $userId)->update(['password'=> Hash::make($request->new_password)]);
        return response()->json(['status' => 'ok', 'message'=>'Password changed Successfully'],201);
        }
         return response()->json(['message'=>'Password Could not be changed '], 400);
     }

}
