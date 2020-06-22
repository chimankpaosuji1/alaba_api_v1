<?php

namespace App\Http\Controllers;

use App\Order;
use App\Discount;
use App\Product;
use App\Sale;
use App\TransactionHistory;
use App\User;
use App\Variant;
use App\Wallet;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
class OrderController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        // return response()->json(['status' => 'ok', 'message' => $request->all()], 200);
        $product = Product::where('product_id', $request->input('product_sku'))->first();

        if (!$product){
            return response()->json(['status' => 'not ok', 'message' =>'Product Not Found'], 404);
        }
        $qty = $product->moq;
        $messages = ['total_qty.min' => 'The quantity must be at least '.$qty.' units.'];
        $rules =['total_qty' => 'required|integer|min:'.$qty];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
        }

        else{
            $date = date('Y-m-d H:i:s');
            $seller_id = $request->seller_Id;
            $buyer_id = Auth::guard()->user()->id;
            $variantsweight = array(); $getamount = array();
            $requestvariants = $request->variants;
            foreach($requestvariants as $variant){
                $variantsweight[] = $variant['quantity'];
                $getamount[] = $this->getAmount($variant['quantity'],$variant['variant_sku']);
            }

            $getDiscount = Discount::where('product_id', $product->id)->first();



            $totalweight1 = array_sum($variantsweight);
            if ($getDiscount){
                if(($date >= $getDiscount->start_date) && ($date <= $getDiscount->end_date)){
                    $getSum = array_sum($getamount);
                    $totalamount = $getSum  - (round($getSum * ($getDiscount->percent / 100),2));
                }
                else{
                    $totalamount = array_sum($getamount);
                }
            }
            else{
                $totalamount = array_sum($getamount);
            }

            $totalweight = array_sum($variantsweight) * $product->single_gross_weight;

            // $totalshippingamount = $totalweight + 1000;
            $totalshippingamount = 0;
            $amountpaid = round($totalamount + $totalshippingamount,2);
            $wallet = Wallet::where('user_id', $buyer_id)->first()->wallet_balance;

            if ($wallet < $amountpaid){
                return response()->json(['status' => 'not ok', 'message' => 'Insufficient Wallet Balance!! Load your wallet to continue'],402);
            }
            $saledata = $request->only(['billing_first_name','billing_last_name','billing_email',
                'billing_phone','billing_address', 'billing_city','billing_country', 'shipping_first_name',
                'shipping_last_name', 'shipping_email', 'shipping_phone', 'shipping_address','shipping_city',
                'shipping_country']);
            $saledata['buyer_id'] = $buyer_id;
            $saledata['seller_id'] = $seller_id;
            $saledata['total_weight'] = $totalweight;
            $saledata['total_amount'] = $totalamount;
            $saledata['total_amount_paid'] = $amountpaid;
            $saledata['total_shipping_amount'] = $totalshippingamount;
            $saledata['total_qty'] = $totalweight1;
            $saledata['status'] = 'Approved';
            $sale_no = $saledata['sale_no'] = 'SL'.date('Ymdhis');

            $newsale = new Sale($saledata);
            $confirm = $newsale->save();

            foreach($requestvariants as $var){
                $order = Order::create(['product_sku' => $product->product_id,
                                        'variant_sku' => $var['variant_sku'],
                                        'quantity' => $var['quantity'],
                                        'amount'=>$var['price'],'total_amount'=>$var['price'] * $var['quantity'],
                                        'seller_id'=> $seller_id,'buyer_id'=>$buyer_id]);
            }
            $orderid = $order->id;
            if (count($requestvariants) > 1) {
                for ($i = 0; $i <= count($requestvariants) - 1; $i++) {
                    $nums[] = $orderid- (10 * $i);
                }
                $newsale->orders()->attach($nums);
            }
            else{
                $newsale->orders()->attach($orderid);
            }
            $newsale->buyers()->attach($buyer_id);
            $newsale->sellers()->attach($seller_id);
            $newsale->products()->attach($product->id);

            Wallet::where('user_id',$buyer_id)->update(['wallet_balance'=>$wallet - $amountpaid]);

            foreach($requestvariants as $variant){
                 $this->deleteVariantQuantity($variant['quantity'],$variant['variant_sku']);
            }
            $transhistory = array();
            $transhistory['user_id'] = $buyer_id;
            $transhistory['type'] = 'order';
            $transhistory['amount'] = $amountpaid;
            $transhistory['description'] = $amountpaid.' Was Deducted from your Wallet to pay for your Sale Order Number '. $sale_no;
            $history = new TransactionHistory($transhistory);
            $history->save();
            $data['user_name'] = Auth::guard()->user()->name;
            $data['year'] = date('Y');
            $this->user_name = $data['user_name'];
            $this->email = Auth::guard()->user()->email;
            $this->sale_no = $data['sale_no'] = $sale_no;
            $data['requestvariants'] = $requestvariants;
            $data['goodsamount'] = $totalamount;
            $data['shippingamount'] = $totalshippingamount;
            $data['totalamount'] = $amountpaid;
            $data['product'] = $product;
            $sellerdetails = User::where('id',$seller_id)->first();
            $data['seller_email'] = $this->seller_email = $sellerdetails->email;
            $data['seller_user_name'] = $this->seller_user_name = $sellerdetails->name;
                Mail::send('ordermail', $data, function($message) {
                    $message->to($this->email,$this->user_name);
                    $message->subject('Order Mail for Sale ID '.$this->sale_no);
                });
                Mail::send('sellerordermail', $data, function($message) {
                    $message->to($this->seller_email,$this->seller_user_name);
                    $message->subject('Order Mail for Sale ID '.$this->sale_no);
                });

            return response()->json(['status' => 'ok', 'message' =>'Order successfully placed!!! Check your mail to continue'], 200);


        }

    }

    public function approveorder(Request $request)
    {
        $user = Auth::guard()->user();
        $sale = Sale::with('orders')->where('sale_no',$request->sale_no)->where('seller_id',$user->id)->first();
        if(!empty($sale)){
            $approvesale = Sale::where('sale_no',$request->sale_no)->update(['seller_status'=>'Approved']);
            $buyer = User::where('id',$sale->buyer_id)->first();
            $this->email = $buyer->email;
            $this->user_name = $buyer->name;
            $this->sale_no = $data['sale_no'] = $request->sale_no;
            Mail::send('buyerconfirmordermail', $data, function($message) {
                $message->to($this->email,$this->user_name);
                $message->subject('Confirm Order Mail for Sale ID '.$this->sale_no);
            });
            return response()->json(['status' => 'ok', 'message' => "Order Approved successfully"], 200);

        }
        else{
            return response()->json(['status' => 'not ok', 'message' => "Invalid Sale Selected"], 400);

        }
    }

    public function deleteVariantQuantity($quantity, $variant_sku)
    {
       $getVariantDetails = Variant::where('variant_sku',$variant_sku)->first();
        Variant::where('variant_sku',$variant_sku)->update(['quantity'=>$getVariantDetails->quantity - $quantity]);
       return true;
    }

    public function getAmount($quantity, $variant_sku)
    {
       $getVariantDetails = Variant::where('variant_sku',$variant_sku)->first();
       return $getVariantDetails->price * $quantity;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getOrderBySale($id)
    {
        //

    }


}
