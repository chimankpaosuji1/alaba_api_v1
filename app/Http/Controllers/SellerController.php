<?php

namespace App\Http\Controllers;

use App\Rfq;
use App\User;
use App\Buyer;
use App\Wallet;
use App\Product;
use Validator;
use App\Seller;
use App\Picture;
use App\Pricing;
use App\Variant;
use App\TransactionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SellerController extends Controller
{
    public function accept(){
        $accept = Seller::where('user_id', Auth::user()->id)->update(['trade_assurance'=>1]);
        if($accept){
            return response()->json(['status'=>'ok', 'message'=>'Trade Assurance Activated Successfully'],201);
        }
    }

    public function addSubaccount(Request $request)
    {
        $user = Auth::guard()->user();
        $userole = $user->roles[0]->id;
        $totalAccount = User::where('seller_id',$user->id)->count();
        // dd($totalProduct);
        if($userole == 52){
                return response()->json(['status' => 'not ok', 'message' => 'You cant add a sub-account!!! Consider Upgrading your Account to Continue'], 422);
        }
        elseif($userole == 72){
            if($totalAccount >= 5){
                return response()->json(['status' => 'not ok', 'message' => 'Maximum addition of sub-account reached!!! Consider Upgrading your Account to Continue'], 422);
            }
        }
        elseif($userole == 62){
            if($totalAccount >= 10){
                return response()->json(['status' => 'not ok', 'message' => 'Maximum addition of sub-account reached!!! Consider Upgrading your Account to Continue Or Contact the Administrator'], 422);
            }
        }
        else{
            return response()->json(['status' => 'not ok', 'message' => 'An error Occured'], 401);
        }
            $rules = [
                'username' => 'required|min:3|unique:users',
                'password' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
            }
            $data = $request->only(['name', 'username','password','email','phone']);
            $data['seller_id'] = $user->id;
            $data['password'] = Hash::make($request->password);
            $newAccount = new User($data);
            $confirm = $newAccount->save();
            wallet::create(['user_id'=> $newAccount->id, 'wallet_balance'=>0]);
            // dd($newAccount);
            if ($confirm){
                $wallet = Wallet::where('user_id', $user->id)->first()->wallet_balance;
                if($wallet < $request->amount){
                    return response()->json(['message' => 'Account Created Successfully but unable to Credit Sub Account due to Insufficient Balance!!!! '], 200);
                }
                $getUserBalance = Wallet::where('user_id',$newAccount->id)->first();
                $confirm = $getUserBalance->update(['wallet_balance' => $getUserBalance->wallet_balance + $request->amount]);
                if ($confirm){
                    $data['user_id'] = $newAccount->id;
                    $data['type'] = 'wallet';
                    $data['amount'] = $request->amount;
                    $data['description'] = $request->amount.' Was Added to your Wallet by '.Auth::guard()->user()->username;
                    $history = new TransactionHistory($data);
                    if($history->save()){
                        $getOldUserBalance = Wallet::where('user_id',$user->id)->first();
                        $getOldUserBalance->update(['wallet_balance' => $getOldUserBalance->wallet_balance - $request->amount]);
                        $data['user_id'] = $user->id;
                        $data['type'] = 'wallet';
                        $data['amount'] = $request->amount;
                        $data['description'] = $request->amount.' Was Deducted From your Wallet by Crediting'.$newAccount->username;
                        $history = new TransactionHistory($data);
                    }
                    return response()->json(['message' =>  'Account Created Successfully And User Credited Successfully'], 200);
                }
            }
            else{
                return response()->json(['message' => 'Unable to Add An account'], 422);
            }
    }


    public function getSeller()
    {
        $userid = Auth::guard()->user()->id;
        $seller = Seller::where('user_id',$userid)->first();
        return response()->json(['seller'=>$seller],200);
    }

    public function getsellerprofile($id){
        $seller = Seller::where('id',$id)->with('products')->get();
        return response()->json(['seller'=>$seller]);
    }

    public function store(Request $request)
    {
        //
        if ($request->username && $request->user_id) {
            $user = User::where('id', $request->user_id)->first();
            if ($user) {
                $rules = [
                    'business_name' => 'required',
                    'business_type' => 'required',
                    'phone' => 'required',
                    'product_category' => 'required',
                    'address' => 'required',
                    'country' => 'required',
                    'city' => 'required',
                    'user_id' => 'required|unique:sellers',

                ];

                $validator = Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
                }

                else {
                    $getExist = Buyer::where('user_id', $request->user_id)->first();
                    if ($getExist) {
                        return response()->json(['status' => 'not ok', 'message' => 'This Account has already registered as a BUYER'], 400);
                    }
                    $data = $request->only(['country', 'city', 'address', 'user_id','business_name','business_type','product_category','phone']);
                    $data['is_basic'] = 1;
                    $seller = new Seller($data);
                    $confirm = $seller->save();

                    if ($confirm) {
                        User::where('id', $request->user_id)->update(['phone'=>$request->phone]);
                        $user->assignRole('seller');
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
        else{
            return response()->json(['status' => 'not ok', 'message' => 'Unprocessable Entity'], 422);
        }
    }

    public function getRfq(){
        $user = Auth::guard()->user();
        $product_ids = Seller::where('user_id', $user->id)->first();
        $product_id = explode(',',$product_ids->product_category);
        $role = $user->roles[0]->id;

        if($role == 52){
            $limit = 1;
        }
        elseif($role == 72){
            $limit = 20;
        }
        elseif($role == 62){
            $limit = 100;
        }
        $data = Rfq::select('*')
            ->whereIn('product_category', $product_id)
            ->orderByRaw(\DB::raw("FIELD(id, ".implode(",",$product_id).")"))
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
        if ($data){
            return response()->json(['status' => 'ok', 'data' => $data , 'message'=>'Rfq needed please provide if you have'], 201);
        }else{
            return response()->json(['status' => 'not ok', 'message' => 'no Rfq'], 403);

        }
    }


    public function getProduct(){

        $products = Product::with('seller')
                            ->with('variants')
                            ->with('categories')
                            ->with('pictures')
                            ->with('pricings')
                            ->where('status', 1)
                            ->where('seller_id', Auth::guard()->user()->id)
                            ->get();
        if($products){
            return response()->json(['status' => 'ok', 'message' => $products], 200);
        }else{
            return response()->json(['status' => 'not ok', 'message' => 'No products added yet'], 403);
        }
    }




    public function getPendingProduct(){
        $products = Product::with('seller')
                            ->with('variants')
                            ->with('categories')
                            ->with('pictures')
                            ->with('pricings')
                            ->where('status', 0)
                            ->where('seller_id', Auth::guard()->user()->id)
                            ->get();
        if($products){
            return response()->json(['status'=> 'ok', 'message'=>$products], 200);
        }
        return response()->json(['status'=>'no ok', 'message'=>'No pending products'], 403);
    }


    public function getProductId($id)
    {
        $products = Product::with('categories')
                            ->with('seller')
                            ->with('variants')
                            ->with('pictures')
                            ->with('pricings')
                            ->where('id', $id)
                            ->where('seller_id', Auth::guard()->user()->id)
                            ->first();
        if($products){
            return response()->json(['status'=>'ok', 'message'=>$products],200);
        }else{
            return response()->json(['status'=>'not ok', 'message'=> 'No Products'], 404);
        }

    }

    public function delete($id)
    {
        //
        $del = Product::where('id',$id)->where('seller_id', Auth::guard()->user()->id );
        if($del->delete()){
            return response()->json(['status'=>'ok', 'message'=>'Product has been deleted successfully'], 200);
        }else{
            return response()->json(['status'=>'Not ok', 'message'=>'An error occur while deleting this product'], 403);
        }
    }


    public function sellerprod($id){
        // ['products'=>function($query){
        //     $query->with('user')
        //             ->with('seller');
        // }]

        // function ($query) {
        //     $query->where('votes', '>', 100)
        //           ->orWhere('title', '=', 'Admin');
        // }
        $products  = User::with(['seller'=>
        function ($query){
            $query->with('products');
        }])->where('username',$id)->get();

        // $products = User::with(['seller'=>function($query){
        //                                  $query->with('products');
        //                              }])->where('id', $id)->get();
        if($products){
            return response()->json(['status'=>'ok', 'message'=>$products],200);
        }
        return response()->json(['status'=>'not ok', 'message'=> 'No active Products'], 402);

    }





}
