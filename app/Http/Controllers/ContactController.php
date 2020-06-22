<?php

namespace App\Http\Controllers;

use App\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $contact = Contact::all();
        if ($contact) {
            return response()->json(['status' => 'ok', 'message' => $contact], 201);
        }
        else{
            return response()->json(['status' => 'Not ok', 'message' => 'no contact messages available'], 404);
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
            'content' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'numeric|nullable'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'not ok', 'message' => $validator->errors()], 400);
        }
        else{
            $data = $request->only(['content','first_name', 'last_name','email','phone']);
            $contact = new Contact($data);
            $confirm = $contact->save();
            Mail::send('contact', $data, function($message) {
                $message->to('alabamarketb2b@gmail.com','Alaba Market');
                $message->subject('Contact Email');
            });
            if($confirm){
                return response()->json(['status' => 'ok', 'message'=>'your message as been sent Successfully '], 201);
            }
            return response()->json(['status' => 'not ok', 'message'=>'your message not sent Successfully '], 400);

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

        $contact = Contact::findOrFail($id);

        if ($contact) {
            return response()->json(['status' => 'ok', 'message' => $contact], 201);
        }
        else{
            return response()->json(['status' => 'Not ok', 'message' => 'no contact messages available'], 404);
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
        $con = Contact::findOrFail($id);
        $con->delete();
        if ($con) {
            return response()->json(['status' => 'ok', 'message'=>'your message as been delete Successfully '], 201);
        }

        return response()->json(['status' => 'not ok', 'message' => 'Message not deleted'], 400);
    }
}
