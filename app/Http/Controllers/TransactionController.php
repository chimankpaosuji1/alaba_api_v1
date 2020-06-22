<?php

namespace App\Http\Controllers;

use Auth;
use App\Transaction;
use App\TransactionHistory;
use App\Wallet;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    //
    public function callback($ref) {
        $curl = curl_init();
        $reference = $ref? $ref : '';
        $yea = Transaction::where('reference', $reference)->first();

        if ($yea && ($yea['status'] != 1)){
            if ($yea['pay_type'] == 'wallet') {
                $curl = curl_init();
                if (!$reference) {
                    return response()->json(['status' => 'not ok', 'message' => 'No Reference Supplied'], 400);
                }
                curl_setopt_array($curl, array(CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
                    CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ["accept: application/json",
                        "authorization: Bearer sk_live_5bc039529addf9f4d6a6890df53c9d6ee4517089", "cache-control: no-cache"],));
                $response = curl_exec($curl);
                $err = curl_error($curl);
                if ($err) {
                    // there was an error contacting the Paystack API
                    return response()->json(['status' => 'not ok', 'message' => $err], 400);
                }
                $tranx = json_decode($response);
                if (!$tranx->status) {
                    // there was an error from the API
                    return response()->json(['status' => 'not ok', 'message' => $tranx->message], 400);
                }
                if ('success' == $tranx->data->status) {
                    // transaction was successful...
                    // please check other things like whether you already gave value for this ref
                    // if the email matches the customer who owns the product etc
                    // Give value
                    $reference = $tranx->data->reference;
                    //select invoice details
                        $update_trans = Transaction::where('reference',$reference)->update(['status'=>1]);

                        if ($update_trans) {
                            $getUserBalance = Wallet::where('user_id',$yea['user_id'])->first();
                            $confirm = $getUserBalance->update(['wallet_balance' => $getUserBalance->wallet_balance + ($tranx->data->amount/100)]);
                            if ($confirm){
                                $data['user_id'] = $yea['user_id'];
                                $data['type'] = $yea['pay_type'];
                                $data['amount'] = $tranx->data->amount/100;
                                $data['description'] =($tranx->data->amount / 100).' Added to your Wallet from Paystack';
                                $history = new TransactionHistory($data);
                                $history->save();
                                return response()->json(['status'=>'ok','message' => 'User Credited Successfully'], 200);
                            }
                        }
                        else{
                            return response()->json(['status' => 'not ok', 'message' => 'Payment Failed Contact the Administrator'], 400);
                        }

                }
                else {
                    return response()->json(['status' => 'not ok', 'message' => 'Payment Not Successful'], 400);
                }
            }
        }
        else{
            return response()->json(['status' => 'not ok', 'message' => 'Invalid Transaction'], 400);
        }
    }

    public function creditUser(Request $request)
    {
        $rules = [
            'reference' => 'required|unique:transactions',
            'amount' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
        }
        $data['reference'] = $request->reference;
        $data['amount'] = $request->amount;
        $data['user_id'] = Auth::guard()->user()->id;
        $data['username'] = Auth::guard()->user()->username;
        $data['email'] = Auth::guard()->user()->email;
        $data['pay_type'] = 'wallet';
        $data['trans_id'] = uniqid();
        $transaction = new Transaction($data);
        $transaction->save();
        return $this->callback($request->reference);

    }
}
