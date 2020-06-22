<?php

namespace App\Http\Controllers;

use App\User;

use App\Buyer;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    //

    public function all_users(){
        $user = User::all();
        if($user){
            return response()->json(['status'=>'ok', 'message'=>$user], 201);
        }else{
            return response()->json(['status'=>'not ok', 'message'=> 'No Users found'], 404);
        }
    }

    public function users_details($id){
        $user = User::where('id', $id)
                        ->with('buyer')
                        ->with('seller')
                        ->first();
        return response()->json(['status'=>'ok', 'message'=>$user], 201);
    }

    public function usersbyrole($role,$roleid){
        if($role == 'buyer'){
            if ($roleid == 32) {
                $user = User::with('buyer')
                        ->whereHas('buyer', function ($query)
                        {
                            $query->where('is_basic',1)->where('is_pro',0);
                        })->get();
            }
            elseif ($roleid == 42) {
                $user = User::with('buyer')
                        ->whereHas('buyer', function ($query)
                        {
                            $query->where('is_basic',1)->where('is_pro',1);
                        })->get();
            }
        }elseif($role == 'seller'){
            if($roleid == 52){
                $user = User::with('seller')
                            ->whereHas('seller', function ($query)
                            {
                                $query->where('is_basic',1)->where('is_premium',0)->where('is_trusted',0);
                            })->get();

            }elseif($roleid == 62){
                $user = User::with('seller')
                                ->whereHas('seller', function ($query)
                                {
                                    $query->where('is_basic',1)->where('is_premium',1);
                                })->get();
            }elseif($roleid == 72){
                $user = User::with('seller')
                                ->whereHas('seller', function ($query)
                                {
                                    $query->where('is_basic',1)->where('is_premium',0)->where('is_trusted',1);
                                })->get();
            }
        }
        return response()->json(['status'=>'ok', 'message'=>$user], 201);
    }


    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->delete()){
            return response()->json(['status' => 'ok', 'message' => 'User deleted successfully'],201);
        }
        else{
            return response()->json(['status' => 'not ok', 'message' => 'User failed deleted successfully'],403);

        }
    }
}
