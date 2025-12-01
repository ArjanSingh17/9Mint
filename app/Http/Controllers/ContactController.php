<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
   public function emptyData(){
    
   }

   public function sendEmail(Request $request){

 $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:100',
            'message' => 'required|string|max:5000'
        ]);

        Mail::raw(
    "Name: {$data['name']}\nEmail: {$data['email']}\n\nMessage:\n{$data['message']}", 
    function ($message) use ($data) {
        $message->to('jahirul.islam200518@gmail.com')
                ->subject('New Contact Form Submission')
                ->replyTo($data['email'], $data['name']);
    }
);

return redirect('/contactUs');
        
   }
}
