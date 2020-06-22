<?php

namespace App\Http\Controllers;

use App\Rfq;
use App\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getsellersales($status = '')
    {
        $userid = Auth::guard()->user()->id;
        if($status){
            $totalSale = Sale::with('orders')->with('products')->where('seller_id',$userid)->where('seller_status',ucfirst($status))->get();
        }
        else{
            $totalSale = Sale::with('orders')->where('seller_id',$userid)->get();
        }
        return response()->json(['status'=>'ok','totalsale' => $totalSale],200);
    }
    public function getsellersalesbysaleno($saleno)
    {
        $userid = Auth::guard()->user()->id;

            $totalSale = Sale::with('orders')->with('products')->where('seller_id',$userid)->where('sale_no',$saleno)->get();

        return response()->json(['status'=>'ok','totalorder' => $totalSale],200);
    }

    public function getbuyersalesbysaleno($saleno)
    {
        $userid = Auth::guard()->user()->id;

            $totalSale = Sale::with('orders')->with('products')->where('buyer_id',$userid)->where('sale_no',$saleno)->get();

        return response()->json(['status'=>'ok','totalorder' => $totalSale],200);
    }

    public function getbuyersales($status = '')
    {
        $userid = Auth::guard()->user()->id;
        if($status){
            $totalSale = Sale::with('orders')->with('products')->where('buyer_id',$userid)->where('status','Approved')->where('seller_status',ucfirst($status))->get();
        }

        else{
            $totalSale = Sale::with('orders')->with('products')->where('buyer_id',$userid)->get();
        }
        return response()->json(['status'=>'ok','totalorder' => $totalSale],200);
    }

    public function getBuyerTotalSaleDetails()
    {
        $userid = Auth::guard()->user()->id;
        $approvedOrder = Sale::where('buyer_id',$userid)->where('status','Approved')->where('seller_status','Approved')->count();
        $pendingOrder = Sale::where('buyer_id',$userid)->where('status','Approved')->where('seller_status','Pending')->count();
        $totalOrder = Sale::where('buyer_id',$userid)->count();
        $totalRfq = Rfq::where('user_id',$userid)->count();
        return response()->json(['status'=>'ok',
                'totalorder' => $totalOrder,
                'approvedorder' => $approvedOrder,
                'pendingorder' => $pendingOrder,
                'totalrfq' => $totalRfq],200);
    }
    public function getTotalSaleDetails()
    {
        $userid = Auth::guard()->user()->id;
        $totalSaleAmount = Sale::where('seller_id',$userid)->where('status','Approved')->where('buyer_status','Approved')->where('seller_status','Approved')->sum('total_amount');
        $totalSale = Sale::where('seller_id',$userid)->where('status','Approved')->where('seller_status','Approved')->count();
        $totalRevenue = $totalSaleAmount - (0.15 * $totalSaleAmount);
        return response()->json(['status'=>'ok',
                'totalsale' => $totalSale,
                'totalrevenue' => number_format((float)$totalRevenue, 2, '.', ''),
                'totalsaleamount' =>number_format((float)$totalSaleAmount, 2, '.', '')],200);
    }

}
