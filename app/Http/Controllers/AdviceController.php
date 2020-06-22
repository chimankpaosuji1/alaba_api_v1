<?php

namespace App\Http\Controllers;

use App\Advice;
use Validator;

use Illuminate\Http\Request;
class AdviceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $advice = Advice::all();
        if($advice){
            return response()->json(['status'=> 'ok ', 'message'=> $advice],200);
        }else{
            return response()->json(['status'=> 'ok', 'message'=> 'Not Advice found in the database'], 403);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'first_name' => 'required',
            'content' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'phone_number' => 'numeric|nullable'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
        }
        else{
            $data = $request->only(['first_name', 'content', 'last_name', 'email', 'phone_number']);
            $confirm = new Advice($data);
            $adv = $confirm->save();
            if($adv){
                return response()->json(['status' => 'ok','message' => 'Advice submitted successfully'], 200);
                
            }
               return response()->json(['status' => 'not ok', 'message' => 'Cannot be submiited'], 403); 
            }
        }
    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $feedback = Advice::find($id);

        if ($feedback) {
          return response()->json(['status' => 'ok','message' => $feedback], 201);
        }
        else{
            return response()->json(['status' => 'Not ok ', 'message' => 'No Content'], 204);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $advice = Advice::findorFail($id);
        if ($advice->delete()){
            return response()->json(['status' => 'ok', 'message' => 'Advice deleted successfully'],201);
        }
        else{
            return response()->json(['status' => 'not ok', 'message' => 'Advice failed  deleted successfully'],403);

        }
    }
}
