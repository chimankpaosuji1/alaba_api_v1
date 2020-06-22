<?php

namespace App\Http\Controllers;
use App\Traits\GetUserWalletBalance;
use App\Buyer;
use App\User;
use App\Role;
use App\Seller;
use App\TransactionHistory;
use App\Subscription;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// use JD\Cloudder\Facades\Cloudder;
use Cloudder;

class UpgradeController extends Controller
{
    use GetUserWalletBalance;
    public function addCertDetails(Request $request)
    {
            $userid = Auth::guard()->user()->id;
            $seller = Seller::where('user_id',$userid);
            if($request->hasFile('trade_image')) {
                $trade_image = $request->file('trade_image');
                $trade_image = $request->file('trade_image')->getClientOriginalName();
                $trade_image = $request->file('trade_image')->getRealPath();
                Cloudder::upload($trade_image, null,  array("public_id" => "trade_images/".uniqid()));

                $image1_url= Cloudder::show(Cloudder::getResult()['url']);
            }
            else{
                $image1_url = '';
            }
            if($request->hasFile('cert_image')) {
                $cert_image = $request->file('cert_image');

                $cert_image = $request->file('cert_image')->getClientOriginalName();

                $cert_image = $request->file('cert_image')->getRealPath();

                Cloudder::upload($cert_image, null, array("public_id" => "cert_images/".uniqid()));

                $image_url2= Cloudder::show(Cloudder::getResult()['url']);
            }
            else{
                $image_url2 = '';
            }
            $confirm = $seller->update(['cert_name'=>$request->cert_name,'cert_ref_no'=>$request->cert_ref_no,'cert_issued_by'=>$request->cert_issued_by,'cert_issued_date'=>$request->cert_issued_date,'cert_image'=>$image_url2, 'cert_desc'=>$request->cert_desc,'trade_name'=>$request->trade_name,'trade_ref_no'=>$request->trade_ref_no,'trade_issued_by'=>$request->trade_issued_by,'trade_issued_date'=>$request->trade_issued_date,'trade_image'=>$image1_url, 'trade_desc'=>$request->trade_desc]);
            if ($confirm) {
                return response()->json(['status' => 'ok', 'message' => 'Account Info Updated Successfully'], 201);
            }
            else{
                return response()->json(['status' => 'not ok', 'message' => 'Unprocessed Entity'], 422);
            }


    }

    public function addFactoryDetails(Request $request)
    {
            $seller = Seller::where('user_id',Auth::guard()->user()->id);
            $confirm = $seller->update(['factory_location'=>$request->factory_location,'factory_size'=>$request->factory_size,'quality_staff'=>$request->quality_staff,'prod_line'=>$request->prod_line,'factory_address'=>$request->factory_address, 'main_market'=>$request->main_market]);
            if ($confirm) {
                return response()->json(['status' => 'ok', 'message' => 'Account Info Updated Successfully'], 201);
            }
            else{
                return response()->json(['status' => 'not ok', 'message' => 'Unprocessed Entity'], 422);
            }


    }

    public function addCompanyDetails(Request $request)
    {
            $userid = Auth::guard()->user()->id;
            $seller = Seller::where('user_id',$userid);
            $rules = [
                'com_logo'     => 'image|mimes:jpeg,bmp,jpg,png|between:1, 7000',
                'com_brochure' => 'image|mimes:jpeg,bmp,jpg,png,PDF,pdf|between:1, 7000',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
            }
            else{
                if($request->hasFile('com_logo')) {
                    $com_logo = $request->file('com_logo');
                    $com_logo = $request->file('com_logo')->getClientOriginalName();
                    $com_logo = $request->file('com_logo')->getRealPath();
                    Cloudder::upload($com_logo, null, array("public_id" => "com_logos/".uniqid()));
                    $image_url1= Cloudder::show(Cloudder::getResult()['url']);

                }
                else{
                    $image_url1 = '';
                }

                if($request->hasFile('com_brochure')) {
                    $com_brochure = $request->file('com_brochure');
                    $com_brochure = $request->file('com_brochure')->getClientOriginalName();
                    $com_brochure = $request->file('com_brochure')->getRealPath();
                    Cloudder::upload($com_brochure, null, array("public_id" => "com_brochure/".uniqid()));
                    $image_url2= Cloudder::show(Cloudder::getResult()['url']);
                }
                else{
                    $image_url2 = '';
                }

                $confirm = $seller->update(['com_logo'=>$image_url1, 'com_intro'=>$request->com_intro,'com_brochure'=>$image_url2]);

                if ($confirm) {
                    return response()->json(['status' => 'ok', 'message' => 'Account Info Updated Successfully'], 201);
                }
                else{
                    return response()->json(['status' => 'not ok', 'message' => 'Unprocessed Entity'], 422);
                }
            }


    }

    public function addBusinessType(Request $request)
    {
        $rules = [
            'business_name' => 'required',
            'business_type' => 'required',
            'product_category' => 'required',
            'address' => 'required',
            'country' => 'required',
            'city' => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
        }
        else{
            $seller = Seller::where('user_id',Auth::guard()->user()->id);
            $confirm = $seller->update(['loc_of_reg'=>$request->loc_of_reg,'year_of_com_reg'=>$request->year_of_com_reg,'com_adv'=>$request->com_adv,'main_product'=>$request->main_update,'country'=>$request->country, 'city'=>$request->city, 'address'=>$request->address,'business_name'=>$request->business_name,'business_type'=>$request->business_type,'product_category'=>$request->product_category,'business_reg_no'=>$request->business_reg_no]);
            if ($request->email || $request->phone) {
                User::where('id',Auth::guard()->user()->id)->update(['email'=>$request->email,'phone'=>$request->phone]);
            }
            if ($confirm) {
                return response()->json(['status' => 'ok', 'message' => 'Account Info Updated Successfully'], 201);
            }
            else{
                return response()->json(['status' => 'not ok', 'message' => 'Unprocessed Entity'], 422);
            }
        }


    }

    public function toProbuyer(Request $request)
    {
        $money_array = ['3'=>'500', '6'=>'1000','1'=>'2000'];
        if (!array_key_exists($request->month, $money_array)) {
            return response()->json(['status' => 'ok', 'message'=>'Invalid Month Selected!!!'], 422);
        }
        $user = Auth::guard()->user()->id;
        $balance = $this->getWalletBalance($user);
        $price = $money_array[$request->month];
        $getQualified = $this->getQualify($balance,$price);
        if (!$getQualified){
            return response()->json(['status' => 'ok', 'message'=>'Insufficient Balance!!! Load your wallet and try again'], 406);
        }
        $role = Role::where('name', 'probuyer')->first()->id;
        DB::table('model_has_roles')->where('model_id',$user)->update(['role_id'=>$role]);
        Buyer::where('user_id',$user)->update(['is_pro'=>1]);
        $this->deductWalletBalance($user, $price);
        $this->addToSubscription($user, $price,$request->month);
        $transhistory = array();
        $transhistory['user_id'] = Auth::guard()->id();
        $transhistory['type'] = 'Account Upgrade';
        $transhistory['amount'] = $price;
        $transhistory['description'] = $price.' Was Deducted from your Wallet to pay for your Account Upgrade to Probuyer ';
        $history = new TransactionHistory($transhistory);
        $confirm = $history->save();
        if ($confirm) {
            return response()->json(['status' => 'ok', 'message'=>'Buyer Role Upgraded Successfully'], 200);
        }
    }

    public function toTrustedSeller(Request $request , $id)
    {
        $role = Role::where('name', 'trustedseller')->first()->id;
        $user = $id;
        DB::table('model_has_roles')->where('model_id',$user)->update(['role_id'=>$role]);
        $confirm = Seller::where('user_id',$user)->update(['is_trusted'=>1]);
        if ($confirm) {
            return response()->json(['status' => 'ok', 'message'=>'Seller Role Upgraded Successfully'], 200);
        }
    }

    public function upgradetoTrustedSeller(Request $request)
    {
        $money_array = ['3'=>'1000', '6'=>'2000','1'=>'5000'];
        if (!array_key_exists($request->month, $money_array)) {
            return response()->json(['status' => 'ok', 'message'=>'Invalid Month Selected!!!'], 422);
        }
        $user = Auth::guard()->user()->id;
        $balance = $this->getWalletBalance($user);
        $price = $money_array[$request->month];
        $getQualified = $this->getQualify($balance,$price);
        if (!$getQualified){
            return response()->json(['status' => 'ok', 'message'=>'Insufficient Balance!!! Load your wallet and try again'], 406);
        }
        $role = Role::where('name', 'trustedseller')->first()->id;
        DB::table('model_has_roles')->where('model_id',$user)->update(['role_id'=>$role]);
        Seller::where('user_id',$user)->update(['is_trusted'=>1]);
        $this->deductWalletBalance($user, $price);
        $this->addToSubscription($user, $price,$request->month);
        $transhistory = array();
        $transhistory['user_id'] = $user;
        $transhistory['type'] = 'Account Upgrade';
        $transhistory['amount'] = $price;
        $transhistory['description'] = $price.' Was Deducted from your Wallet to pay for your Account Upgrade to Trusted Seller ';
        $history = new TransactionHistory($transhistory);
        $confirm = $history->save();

        if ($confirm) {
            return response()->json(['status' => 'ok', 'message'=>'Seller Role Upgraded Successfully'], 200);
        }
    }

    public function toPremiumSeller(Request $request)
    {
        $money_array = ['3'=>'5000', '6'=>'7000','1'=>'10000'];
            if (!array_key_exists($request->month, $money_array)) {
                return response()->json(['status' => 'ok', 'message'=>'Invalid Month Selected!!!'], 422);
            }
        $user = Auth::guard()->user()->id;
        $balance = $this->getWalletBalance($user);
        $price = $money_array[$request->month];
        $getQualified = $this->getQualify($balance,$price);
            if (!$getQualified){
                return response()->json(['status' => 'ok', 'message'=>'Insufficient Balance!!! Load your wallet and try again'], 406);
            }
        $role = Role::where('name', 'premiumseller')->first()->id;
        DB::table('model_has_roles')->where('model_id',$user)->update(['role_id'=>$role]);
        Seller::where('user_id',$user)->update(['is_premium'=>1]);
            $this->deductWalletBalance($user, $price);
            $this->addToSubscription($user, $price,$request->month);
        $transhistory = array();
        $transhistory['user_id'] = $user;
        $transhistory['type'] = 'Account Upgrade';
        $transhistory['amount'] = $price;
        $transhistory['description'] = $price.' Was Deducted from your Wallet to pay for your Account Upgrade to Premium Seller ';
        $history = new TransactionHistory($transhistory);
        $confirm = $history->save();
        if ($confirm) {
            return response()->json(['status' => 'ok', 'message'=>'Seller Role Upgraded Successfully'], 200);
        }
    }

    public function getQualify($balance,$price)
    {
        if ($balance >= $price){
            return true;
        }
        return false;
    }

    public function addToSubscription($userid,$amount,$month)
    {
        $subscription = array();
        $subscription['user_id'] = $userid;
        $subscription['status'] = 1;
        $subscription['amount'] = $amount;
        if ($month == 3) {
            $monthToAdd = '+3 month';
        }
        elseif ($month == 6) {
            $monthToAdd = '+6 month';
        }
        elseif ($month == 1) {
            $monthToAdd = '+12 month';
        }
        $subscription['start_date'] = date('Y-m-d H:i:s');
        $subscription['end_date'] = date('Y-m-d H:i:s', strtotime($monthToAdd));
        $save = new Subscription($subscription);
        $save->save();
    }
}
