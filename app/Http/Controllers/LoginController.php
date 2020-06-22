<?php

namespace App\Http\Controllers;

use ipfinder\ipfinder\IPfinder;
use App\Events\SendPasswordResetLinkEvent;
use App\Islocked;
use App\PasswordResets;
//use geoip;
use App\User;
use App\Seller;
use App\Buyer;
use App\Login;
use App\Subscription;
use Validator;
use App\AccountActivation;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','clientip']]);
    }
    public function clientip(Request $request)
    {
        //        $client = new IPfinder('b155ddf27332f0e54647de5526568496a12b2ee7');
        //
        //        $details = $client->getAddressInfo(geoip()->getLocation($request->ip())->ip);
        //        dd($details->);
        //        return strtok(exec('getmac'), ' ');
        //        dd($request->ip());


        return (($request->getClientIp(true)));

        $apiKey = "c3a069253e544cd6b8e0d67a4d7abd3f";
        $ip = geoip()->getLocation($request->ip())->ip;
        $location = $this->get_geolocation($apiKey, $ip);
        $decodedLocation = json_decode($location, true);

        echo "<pre>";
        print_r($decodedLocation);
        echo "</pre>";
    }
    function get_geolocation($apiKey, $ip, $lang = "en", $fields = "*", $excludes = "") {
        $url = "https://api.ipgeolocation.io/ipgeo?apiKey=".$apiKey."&ip=".$ip."&lang=".$lang."&fields=".$fields."&excludes=".$excludes;
        $cURL = curl_init();
        curl_setopt($cURL, CURLOPT_URL, $url);
        curl_setopt($cURL, CURLOPT_HTTPGET, true);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json'
        ));
        return curl_exec($cURL);
    }
    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function login(Request $request){
        $apiKey = "c3a069253e544cd6b8e0d67a4d7abd3f";
        $ip = geoip()->getLocation($request->ip())->ip;
        $location = $this->get_geolocation($apiKey, $ip);
        $decodedLocation = json_decode($location, true);
        $user = User::where('username',$request->username)->orwhere('email',$request->username)->first();
        if($user){
            $islocked = Islocked::where('user_id',$user->id)->where('status',1)->first();
            if($islocked){
                return response()->json(['status'=>'not ok', 'message'=>'Account is Temporary Locked'],  401);
            }

            $login_type = filter_var($request->input('username'), FILTER_VALIDATE_EMAIL ) ? 'email' : 'username';

         $credentials = Auth::guard()->attempt(array($login_type => $request->username,  'password' => $request->password));
            if($credentials){
                // dd($credentials);
                $data = array();
                $data['ip'] = $ip;
                $data['status'] = 1;
                $data['user_id'] = $user->id;
                $data['country'] = $decodedLocation['country_name'];
                $data['region'] = $decodedLocation['state_prov'];
                $log = new Login($data);
                $save = $log->save();
                Login::where('user_id', $user->id)->where('status', 0)->delete();
                // $this->checkAccountActive($user->id);
                return $this->respondWithToken($credentials);
            }
                $data = array();
                $data['ip'] = $ip;
                $data['status'] = 0;
                $data['user_id'] = $user->id;
                $data['country'] = $decodedLocation['country_name'];
                $data['region'] = $decodedLocation['state_prov'];
                $nooflogin = Login::where('status',0)->where('user_id',$user->id)->count();
                if($nooflogin >= 5){

                    Login::where('user_id', $user->id)->where('status',0)->delete();
                    $getIslocked = Islocked::where('user_id',$user->id)->first();
                    if ($getIslocked){
                        Islocked::where('user_id',$user->id)->update(['status'=>1]);
                    }
                    else{
                        $islock = new Islocked();
                        $islock->user_id = $user->id;
                        $islock->reason = 'Too Many Wrong Logins';
                        $islock->save();
                    }
                    $reset_token = $user->username . strtotime(\Carbon\Carbon::now(1));
                    $data['token'] = md5($reset_token);
                    $data['email'] = $user->email;
                    $data['reason'] = 'account locked';
                    $passwordReset = new PasswordResets($data);
                    $passwordReset->created_at = date("Y-m-d H:i:s", strtotime('+24 hours'));
                    $confirm = $passwordReset->save();
                    $passwordReset['user_name'] = $user->username;
                    event(new SendPasswordResetLinkEvent($passwordReset));
                    return response()->json(['status'=>'not ok', 'message'=>'Account is Temporarily Locked!!! Check your mail or Contact the Administrator to continue'],  401);
                }
                $log = new Login($data);
                $save = $log->save();
                return response()->json(['message' => 'Incorrect Username Or Password'], 401);
        }
        return response()->json(['message' => 'Incorrect Username Or Password'], 401);
    }

    public function checkAccountActive($user){
        $role = DB::table('model_has_roles')->where('model_id',$user)->first()->role_id;
        if(!$role){
            return false;
        }
        elseif ($role == 42) {
            $enddate = Subscription::where('user_id',$user)->latest('created_at')->first()->end_date;
            if (date('Y-m-d H:i:s') > $enddate ) {
                DB::table('model_has_roles')->where('model_id',$user)->update(['role_id'=> 4]);
                Buyer::where('user_id',$user)->update(['is_pro'=>0]);
                Subscription::where('user_id',$user)->update(['status'=>0]);
            }
        }
        elseif ($role == 62 || $role == 72) {
            $enddate = Subscription::where('user_id',$user)->latest('created_at')->first()->end_date;
            if (date('Y-m-d H:i:s') > $enddate ) {
                DB::table('model_has_roles')->where('model_id',$user)->update(['role_id'=> 6]);
                Seller::where('user_id',$user)->update(['is_trusted'=>0,'is_premium'=>0]);
                Subscription::where('user_id',$user)->update(['status'=>0]);
            }
        }

    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthUser()
    {
        $user = $this->guard()->user();
        // dd($user[0]->roles->id);
        foreach ($user->roles as $key => $value) {
             $user['role'] = $value;
        }
        $role = ($user->role)?$user->role->id:0;
        if ($role == 32 || $role == 42){
            $details = User::with('buyer')->where('id',$user->id)->first();
        }
        elseif ($role == 52 || $role == 62 || $role == 72){
            $details = User::with('seller')->where('id',$user->id)->first();
        }
        else{
            $details = null;
        }
        $permission = $user->getPermissionsViaRoles();
        $email = AccountActivation::where('user_id', $user->id)->first();
        $emailStatus = ($email)?$email->status:0;
        return response()->json(['user'=>$user,'emailStatus'=>$emailStatus, 'role'=>$role,'permission'=>$permission, 'details'=>$details]);
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        // return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(JWTAuth $jwt_auth)
    {
        //
        if ($jwt_auth) {
            return $jwt_auth->parseToken()->toUser();
        }

        return false;
    }

    public function changepassword(Request $request){
            $rules = [
                'new_password' => 'required',
                'new_confirm_password' => 'same:new_password',
             ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
        }
        if(!Hash::check($request->password, auth()->guard()->user()->password)){
           return response()->json(['status'=>'not ok','message'=>'Old Password didn\'t match'],406);
        }
        $userId = Auth::guard()->user()->id;
        $data['name'] = Auth::guard()->user()->name;
        if($userId){
            $email = Auth::guard()->user()->email;
            User::find($userId)->update(['password'=> Hash::make($request->new_password)]);
            // Mail::send('password', $data, function($message) {
            //     $message->to($email,'Password Changed');
            //     $message->subject('Password Changed Alert');
            // });
            return response()->json(['status' => 'ok', 'message'=>'Password changed Successfully'],201);
          }
            return response()->json(['message'=>'Password Could not be changed '], 400);


    }
}
