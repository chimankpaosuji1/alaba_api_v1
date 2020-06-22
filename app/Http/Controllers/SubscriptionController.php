<?php

namespace App\Http\Controllers;
use Auth;
use Illuminate\Http\Request;
use App\Subscription;

class SubscriptionController extends Controller
{
    //
    public function getUserSubscriptions()
    {
      
        $userid = Auth::guard()->id();
        $allSubscriptions = Subscription::where('user_id',$userid)->get();
        return response()->json(['subscriptions'=>$allSubscriptions]);
    }
}
