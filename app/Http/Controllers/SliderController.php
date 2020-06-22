<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use JD\Cloudder\Facades\Cloudder;
use App\Slider;
class SliderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list =  Slider::all();
        if($list){
            return response()->json(['status'=>'ok', 'message'=>$list], 200);
        }else{
            return response()->json(['status'=>'ok', 'message'=>'No slider in the database'],200);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $rules = [
            'slider_text' =>  'required',
            'slider_image' => 'required|mimes:jpeg,bmp,jpg,png|between:1, 7000'
        ];
        $validator = Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
                }else{
                    if($request->hasFile('slider_image')) {
                        $slider_image = $request->file('slider_image');
                        Cloudder::upload($slider_image, null,array("public_id" => "slider/".uniqid(),  "width"=>600, "height"=>600, "crop"=>"scale", "fetch_format"=>"auto", "quality"=>"auto",
                        "flags"=>array("progressive", "progressive:semi", "progressive:steep"),
                       ));

                        $image_url= Cloudder::secureShow(Cloudder::getResult()['secure_url']);
                    }
                }
                $data = $request->only('slider_text', 'slider_image');
                $data['slider_image'] = $image_url;
                $slider= new Slider($data);
                $confirm = $slider->save();
                if ($confirm){
                    return response()->json(['status' => 'ok', 'message' => 'Slider Uploaded successfully'], 200);
                }
                return response()->json(['status' => 'not ok', 'message' => 'Slider not submitted '], 400);
            }





    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $rules = [
            'slider_text' =>  'required',
            'slider_image' => 'required|mimes:jpeg,bmp,jpg,png|between:1, 7000',
            'status' =>  'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
        }else{
            if($request->hasFile('slider_image')) {
                $slider_image = $request->file('slider_image');
                Cloudder::upload($slider_image, null,array("public_id" => "slider/".uniqid(), "width"=>600, "height"=>600, "crop"=>"scale", "fetch_format"=>"auto", "quality"=>"auto", "flags"=>array("progressive", "progressive:semi", "progressive:steep")));
                $image_url= Cloudder::show(Cloudder::getResult()['url']);
            }else{
                $image_url = '';
            }
            if($slide = Slider::where('id',$request->id)->first()) {
                $data = $request->only(['slider_text','slider_image']);
                $data['slider_image'] = $image_url;
                $confirm = $slide->update($data);
                if ($confirm) {
                    return response()->json(['status' => 'ok', 'message'=>'Slider Updated Successfully'], 201);
                }
            }
            else{
                return response()->json(['status' => 'ok', 'message'=>'Slider Not Updated'], 404);
            }

        }


    }

    public function activate(Request $request, $id){

            if ($slide = Slider::where('id',$request->id)->first()) {
                $data['status'] =  1;
                $data = $request->only(['status']);
                $confirm = $slide->update($data);
                if ($confirm) {
                    return response()->json(['status' => 'ok', 'message'=>'Slider  Activated  Successfully'], 200);
                }else{
                    return response()->json(['status' => 'not ok', 'message'=>'Slider Not Activated'], 400);
                }
            }

    }

    public function activateds(){
        $slider = Slider::where('status', '1')->cursor();
        if($slider){
            return response()->json(['status' => 'ok', 'message'=>$slider],200);
        }else{
            return response()->json(['status' => 'not ok', 'message'=> 'slider not active']);
        }
    }


    public function deactivate(Request $request, $id){
        $rules = [
        'status'
        ];

        $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
            }else{
                if ($slide = Slider::where('id',$request->id)->first()) {
                    $data['status'] =  0;
                    $data = $request->only(['status']);
                    $confirm = $slide->update($data);
                    if ($confirm) {
                        return response()->json(['status' => 'ok', 'message'=>'Slider Deactivated Successfully'], 200);
                    }
                    else{
                        return response()->json(['status' => 'not ok', 'message'=>'Slider Not Deactivated'], 400);
                    }
                }
            }
}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $slide = Slider::findOrFail($id);
        if ($slide) {
            $confirm = $slide->delete();
            if ($confirm) {
                return response()->json(['status' => 'ok', 'message'=>'Slider Deleted Successfully'], 200);
            }
        }
        else{
            return response()->json(['status' => 'Not ok', 'message'=>'Slider Not Deleted'], 404);
        }
    }
}
