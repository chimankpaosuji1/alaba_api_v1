<?php

namespace App\Http\Controllers;

use App\TransactionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionHistoryController extends Controller
{
    //

    public function getUserTransaction()
    {
        $userid = Auth::guard()->user()->id;
        $history = TransactionHistory::where('user_id',$userid)->get();
        $getHistory = $history?$history:0;
        return response()->json(['message' => $getHistory], 201);
    }
}
