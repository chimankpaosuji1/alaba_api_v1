<?php

namespace App\Http\Controllers;
use App\Rfq;
//use App\User;
use Validator;
use Illuminate\Http\Request;
// use JD\Cloudder\Facades\Cloudder;
use Cloudder;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class RfqController extends Controller
{
    public function  __construct()
    {
        $this->middleware('auth:api');
    }


    public function index()
    {
        //
        $product =  Rfq::with('categories')->get();
        if ($product){
            return response()->json(['status'=> 'ok', 'message' => $product], 201);
        }
        return response()->json(['status'=> 'ok', 'message' => 'No Rfq available'], 403);
    }

//store ll rfq from buyers to the database
    public function store(Request $request)
    {

            $user = User::where('id', Auth::guard()->user()->id)->first();
//            Auth::guard()->user();
            if($user){
                $rules = [
                    'product_keyword' => 'required|string',
                    'product_category' => 'required',
                    'product_quantity' => 'required',
                    'description' => 'required',
                    'product_unit' => 'required',
                    // 'rfq_image' => 'mimes:jpeg,bmp,jpg,png|between:1, 7000',
                ];

                $validator = Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
                }
                else{
                    if($request->hasFile('rfq_image')) {
                        $rfq_image = $request->file('rfq_image');
                        $rfq_image = $request->file('rfq_image')->getClientOriginalName                                                                                 ();
                        $rfq_image = $request->file('rfq_image')->getRealPath();
                        Cloudder::upload($rfq_image, null,array("public_id" => "rfq_images/".uniqid(), "fetch_format"=>"auto", "quality"=>"auto", "flags"=>array("progressive", "progressive:semi", "progressive:steep")));
                        $image_url = Cloudder::show(Cloudder::getResult()['secure_url']);

                    }

                    else{
                        $image_url = '';
                    }

                    $user = Auth::guard()->user()->username;
                    $user2 = Auth::guard()->user()->email;
                    $data = $request->only('product_keyword','product_category', 'product_quantity','description','product_unit','rfq_image');
                    $data['username'] = $user;
                    $data['email'] = $user2;
                    $data['user_id'] = Auth::guard()->user()->id;
                    $data['rfq_image'] = $image_url;
                    $rfq= new Rfq($data);
                    $confirm = $rfq->save();
                    if ($confirm){
                        return  response()->json(['status' => 'ok', 'message' => 'rfq submitted successfully'], 201);
                    }
                    return  response()->json(['status' => 'not ok', 'message' => 'rfq not submitted '], 400);
                }
            }
            return response()->json(['status' => 'not ok', 'message' => 'no account found'], 400);
    }



    //show all rfqs for  seller

    public function list(){
        $userId = Auth::guard()->user()->id;
        $lis = Rfq::where('user_id', $userId)->first();
        if($lis){
                return response()->json(['status' => 'ok', 'message' => $lis], 201);
             }
        else{
            return response()->json(['status' => 'no ok', 'message' => 'Rfq Not Found'], 404);
        }

    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
//selller reply the buyer  about the rfq availability
    public function reply(Request $request, $id)
    {
        $input = Rfq::findOrFail($id);
        if ($request->username){
            $user =  $user = User::where('username', Auth::guard()->user()->id)->first();
            if ($user){
                $rules = [
                    'reply' => 'required',
                    'status' => 'required'
                ];
                $validator = Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
                }else{

                    $input->status = 1;
                    $input = $request->reply;

                    if($input->update($input)){
                        return response()->json(['status' => 'ok', 'message' => 'it has been updated successfully'],201);
                    }
                    return response()->json(['status' => 'not ok', 'message' => 'could not be upadated '],400);

                }

            }

        }
    }

//    /**
//     * Remove the specified resource from storage.
//     *
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
    public function destroy($id)
    {
        $qoute = Rfq::findOrFail($id);
        if ($qoute->delete()){
            return response()->json(['status' => 'ok', 'message' => 'Rfq deleted successfully'],201);
        }
        else{
            return response()->json(['status' => 'not ok', 'message' => 'Rfq failed  deleted successfully'],403);

        }
    }
}
