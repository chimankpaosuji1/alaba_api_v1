<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Faq;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $search = '';
        if ($request->input('search') != null) {
            $search = $request->input('search');
        }
        $faqs = Faq::where(function ($query) use ($search) {
            $query->where('question', 'like', '%' . $search . '%')
                ->orWhere('answer', 'like', '%' . $search . '%');
        });

        //date range
        $faqs = $faqs->get();

        if ($faqs->count()) {
            return $faqs;
        }

        return [];
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
        $rules =  [
            'question' => 'required',
            'answer' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
        }
        else {
            $data = $request->only(['answer','question']);
            $faq = new Faq($data);
            $confirm = $faq->save();
            if ($confirm) {
                return response()->json(['status' => 'ok', 'message'=>'Faq Added Successfully'], 201);
            }
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

        $rules =  [
            'question' => 'required',
            'answer' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
        }
        else {
            if ($faq = Faq::where('id',$request->id)->first()) {
                $data = $request->only(['answer','question']);
                $confirm = $faq->update(['answer'=> $request->answer,'question'=>$request->question]);
                if ($confirm) {
                    return response()->json(['status' => 'ok', 'message'=>'Faq Updated Successfully'], 201);
                }
            }
            else{
                return response()->json(['status' => 'ok', 'message'=>'Faq Not Found'], 404);
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
        //
        $faq = Faq::findOrFail($id);
        if ($faq) {
            $confirm = $faq->delete();
            if ($confirm) {
                return response()->json(['status' => 'ok', 'message'=>'Faq Deleted Successfully'], 200);
            }
        }
        else{
            return response()->json(['status' => 'ok', 'message'=>'Faq Not Found'], 404);
        }
    }
}
