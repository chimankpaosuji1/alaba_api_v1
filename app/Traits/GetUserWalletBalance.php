<?php

namespace App\Traits;

use App\Wallet;

trait GetUserWalletBalance{
    public function getWalletBalance($id)
    {
        return Wallet::where('user_id',$id)->first()->wallet_balance;
    }
    
    public function deductWalletBalance($id,$amount)
    {
        $balance = Wallet::where('user_id',$id)->first()->wallet_balance;
        $newbalance = $balance - $amount;
        return Wallet::where('user_id',$id)->update(['wallet_balance'=>$newbalance]);
    }
}
