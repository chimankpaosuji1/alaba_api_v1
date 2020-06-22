<?php

namespace App\Http\Controllers;

use App\Page;
use Illuminate\Http\Request;
use Validator;
class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //

        $page = Page::all();
        // dd($page);
        return $page->count() ? $page : [];
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
                'pagename' => 'required',
                'pageslug' => 'required',
                'content' => 'required',
            ];

           $validator = Validator::make($request->all(), $rules);
           if ($validator->fails()) {
               return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
           }
           else {
               $data = $request->only(['pagename', 'pageslug', 'content']);
               $page = new Page($data);
               $confirm = $page->save();
               if ($confirm) {
                return response()->json(['status' => 'ok', 'message'=>'Page Added Successfully!'], 201);
                }
                else{
                    return response()->json(['status' => 'not ok', 'message' => 'Error Adding Page '], 422);
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
        $getPage = Page::where('id', strtolower($id))->orWhere('pageslug',strtolower($id))->first();
        if ($getPage) {
            return response()->json(['status' => 'ok', 'message'=>$getPage], 201);
        }
        else{
            return response()->json(['status' => 'not ok', 'message'=>'Page Not Found'], 404);
        }
    }


    public function update(Request $request,$id)
    {
        //
        $getPage = Page::where('id', strtolower($id))->orWhere('pageslug',strtolower($id))->first();
        if ($getPage) {
            $rules = [
                'content' => 'required',
            ];

           $validator = Validator::make($request->all(), $rules);
           if ($validator->fails()) {
               return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
           }
           else {
               $data = $request->only(['pagename', 'pageslug', 'content']);
               $confirm = $getPage->update(['pagename' => $request->pagename,'pageslug' => $request->pageslug,'content' => $request->content]);
               if ($confirm) {
                return response()->json(['status' => 'ok', 'message'=>'Page Updated Successfully!'], 201);
                }
                else{
                    return response()->json(['status' => 'not ok', 'message' => 'Error Updating Page '], 422);
                }
           }
        }
        else{
            return response()->json(['status' => 'not ok', 'message'=>'Page Not Found'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  strtolower($id)
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $getPage = Page::where('id', strtolower($id))->orWhere('pageslug',strtolower($id))->first();
        if ($getPage) {
            $confirm = $getPage->delete();
            if ($confirm) {
            return response()->json(['status' => 'ok', 'message'=>'Page Deleted Successfully!'], 201);
            }
            else{
                return response()->json(['status' => 'not ok', 'message' => 'Error Deleting Page '], 422);
            }
        }
        else{
            return response()->json(['status' => 'not ok', 'message'=>'Page Not Found'], 404);
        }
    }
}
