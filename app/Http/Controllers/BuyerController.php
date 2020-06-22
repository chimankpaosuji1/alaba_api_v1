<?php

namespace App\Http\Controllers;

use App\Buyer;
use App\Sale;
use App\Seller;
use App\Role;
use App\User;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class BuyerController extends Controller
{
    public function update(Request $request){
        $userid = Auth::user()->id;
        User::where('id',$userid)->update(['name'=>$request->name,'username'=>$request->username,'phone'=>$request->phone]);
        Buyer::where('user_id',$userid)->update(['country'=>$request->country,'city'=>$request->city,'address'=>$request->address]);
        return response()->json(['status'=>'ok','message'=>'Account Updated Successfully'], 200);
    }

    public function store(Request $request)
    {
        //
        if ($request->username && $request->user_id) {
            $user = User::where('id', $request->user_id)->first();
            if ($user) {
                $rules = [
                    'country' => 'required',
                    'city' => 'required',
                    'address' => 'required',
                    'user_id' => 'required|unique:buyers',

                ];

                $validator = Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
                }
                else {
                    $getExist = Seller::where('user_id', $request->user_id)->first();
                    if ($getExist) {
                        return response()->json(['status' => 'not ok', 'message' => 'This Account has already registered as a SELLER'], 400);
                    }
                    $data = $request->only(['country', 'city', 'address', 'user_id']);
                    $data['is_basic'] = 1;
                    $buyer = new Buyer($data);
                    $confirm = $buyer->save();

                    if ($confirm) {
                        $user->assignRole('buyer');
                         Wallet::create([
                            'user_id' => $request->user_id,
                            'wallet_balance' => 0,
                        ]);
                        return response()->json(['status' => 'ok', 'message' => 'Account Confirmed Successfully'], 201);
                    }
                    else{
                        return response()->json(['status' => 'not ok', 'message' => 'Unprocessed Entity'], 422);
                    }

                }
            }
            else{
                return response()->json(['status' => 'not ok', 'message' => 'Account Not Found'], 404);
            }
        }
        else {
            return response()->json(['status' => 'not ok', 'message' => 'Unprocessable Entity'], 422);
        }
    }

    public function getBuyerSale()
    {
        $buyer_id = Auth::guard()->id();
        $getSales = Buyer::with('sales')
            ->where('user_id',$buyer_id)
            ->tosql();
        dd($getSales);
    }

    public function getBuyerSales()
    {
        $buyer_id = Auth::guard()->id();
        $getSales = Sale::with('buyers')
            ->with('products')
            ->where('buyer_id',$buyer_id)
            ->tosql();
        dd($getSales);
    }

    public function getApprovedBuyerSales()
    {
        $buyer_id = Auth::guard()->id();
        $getSales = Sale::with('buyers')
            ->with('products')
            ->where('buyer_id',$buyer_id)
            ->where('buyer_status','Approved')
            ->tosql();
        dd($getSales);
    }

    public function getPendingBuyerSales()
    {
        $buyer_id = Auth::guard()->id();
        $getSales = Sale::with('buyers')
            ->with('products')
            ->where('buyer_id',$buyer_id)
            ->where('buyer_status','Pending')
            ->tosql();
        dd($getSales);
    }
}
