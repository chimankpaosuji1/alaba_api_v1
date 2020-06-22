<?php

namespace App\Http\Middleware;

use App\Login;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckLastLoginCountry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $apiKey = "c3a069253e544cd6b8e0d67a4d7abd3f";
        $ip = geoip()->getLocation($request->ip())->ip;
        $location = $this->get_geolocation($apiKey, $ip);
        $decodedLocation = json_decode($location, true);
        $userid = Auth::guard()->id();
        $getLastCountry = Login::where('user_id', $userid)->latest('created_at')->first();
        if($getLastCountry){
            if ($getLastCountry->region != $decodedLocation['state_prov']){
                $this->guard()->logout();
                return response()->json(['message' => 'Successfully logged out'], 200);
            }
        }
        return $next($request);
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
}
