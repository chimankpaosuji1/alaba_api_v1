<?php

namespace App\Http\Controllers;

use App\User;
use Validator;
use App\Product;
use App\MessageCenter;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class MessagecenterController extends Controller
{
    //

    public function sendmessage(Request $request){

        $rules = [
            'content' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
        }else{
            $user = Auth::guard()->user()->id;
            $user2 = Auth::guard()->user()->email;
            $seller_id = $request->seller_id;
            $product_id = $request->product_id;
            $data = $request->only('content','seller_id', 'buyer_id','read','product_id');
            $data['buyer_id'] =  $user;
            $data['seller_id'] = $seller_id;
            $data['read'] = 0;
            $data['reply'] = '';
            $data['product_id'] = $product_id ;
            $message = new MessageCenter($data);
            $con = $message->save();
            $data['username'] = $this->user_name = Auth::guard()->user()->username;
            $buyer_details = User::where('id', $user)->first();
            $data['email'] = $this->email = Auth::guard()->user()->email;
            $this->message = $data['content'];
            $product = Product::where('id', $product_id)->first();
            $data['product_name'] = $this->product_name = $product->product_name;
            $seller_details = User::where('id',$seller_id)->first();
            $data['seller_email'] = $this->seller_email = $seller_details->email;
            Mail::send('sent', $data, function($message) {
                $message->to($this->email,$this->user_name);
                $message->subject('Product Inquiry'.$this->product_name);
            });
            Mail::send('message', $data, function($message) {
                $message->to($this->seller_email);
                $message->subject('Product Inquiry '.$this->product_name);
            });

            if($con){
                return response()->json(['status'=> 'ok', 'message'=>'Message sent to the seller successfully'],201);
            }else{
                return response()->json(['status' => 'not ok', 'message'=>'An error occured while sending.. Try again'], 403);
            }

            // }

        }
    }

    public function buyerGetUnreadMessage(){
        $user = Auth::guard()->user();
        $message = MessageCenter::with('product')->where('buyer_id',$user->id)->where('read',0)->get();
        return response()->json(['status'=>'ok', 'message' => $message],200);
    }

    public function buyerGetReadMessage(){
        $user = Auth::guard()->user();
        $message = MessageCenter::with('product')->where('buyer_id',$user->id)->where('read',1)->get();
        return response()->json(['status'=>'ok', 'message' => $message],200);
    }

    public function reply(Request $request){
        $user = Auth::guard()->user();
        $rules = [
            'reply' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
        }else{
            $getMessage = MessageCenter::where('id',$request->id);
            $getMessageDetails = $getMessage->first();
            if($getMessageDetails->seller_id == $user->id){
                $updateMessage = $getMessage->update(['reply' => $request->reply,'read'=>1]);
                if ($updateMessage) {
                    $getProductDetails = Product::where('id',$getMessageDetails->product_id)->first();
                    $getBuyerDetails = User::where('id',$getMessageDetails->buyer_id)->first();
                    $data['buyer_name'] = $this->buyer_name = $getBuyerDetails->name;
                    $data['buyer_email'] = $this->buyer_email = $getBuyerDetails->email;
                    $data['seller_name'] = $this->seller_name = $user->name;
                    $data['seller_email'] = $this->seller_email = $user->email;
                    $data['product_name'] = $this->product_name = $getProductDetails->product_name;
                    $data['reply'] = $request->reply;
                    $send = Mail::send('reply', $data, function($message) {
                        $message->to($this->buyer_email,$this->buyer_name);
                        $message->subject('Product Inquiry for '.$this->product_name);
                    });
                    if($send){
                        return response()->json(['status'=> 'ok', 'message'=>'Message sent to the Buyer successfully'],201);
                    }else{
                        return response()->json(['status' => 'not ok', 'message'=>'An error occured while sending.. Try again'], 403);
                    }
                }

            }
            return response()->json(['status' => 'not ok', 'message'=>'An error occured while sending.. Try again'], 422);

        }
    }



    public function sellerGetUnreadMessage(){
        $user = Auth::guard()->user();
        $message = MessageCenter::with('product')->where('seller_id',$user->id)->where('read',0)->get();
        return response()->json(['status'=>'ok', 'message' => $message],200);
    }


    public function viewmessage($id){
        $user = Auth::guard()->user();
        $message = MessageCenter::with('product')->where('id', $id)->where('seller_id',$user->id)->get();
        return response()->json(['status'=>'ok', 'message' => $message],200);
    }

    public function sellerGetReadMessage(){
        $user = Auth::guard()->user();
        $message = MessageCenter::with('product')->where('seller_id',$user->id)->where('read',1)->get();
        return response()->json(['status'=>'ok', 'message' => $message],200);
    }


}
