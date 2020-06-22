<?php

namespace App\Http\Controllers;

use App\Rfq;
use App\User;
use App\Buyer;
use App\Seller;
use App\Product;
use App\AccountActivation;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    //

    public function getAdminStats()
    {
        $users = User::all()->count();
        $pending_activation = AccountActivation::where('status', 0)->count();
        $active_activation = AccountActivation::where('status', 1)->count();

        $products = Product::all()->count();
        $pending = Product::where('status', 0)->count();
        $basic_buyer = Buyer::where('is_basic', 1)->count();
        $is_pro = Buyer::where('is_pro', 1)->count();
        $basic_seller = Seller::where('is_basic', 1)->count();
        $gold   = Seller::where('is_trusted', 1)->count();
        $is_premium = Seller::where('is_premium', 1)->count();
        $is_pro = Buyer::where('is_pro', 1)->count();
        $active  = Product::where('status', 1)->count();
        $rfqs = Rfq::all()->count();
        return response(['status'=>'ok', 'users'=>$users, 'pending_activation'=>$pending_activation, 'confirmed_users'=>$active_activation,'basic_seller'=>$basic_seller, 'gold_seller'=>$gold, 'is_premium'=>$is_premium, 'basic_buyer'=>$basic_buyer, 'is_pro_buyer'=>$is_pro,  'product_active'=>$active, 'pending_product'=>$pending ,'products'=>$products, 'rfqs'=>$rfqs], 200);
    }
}
