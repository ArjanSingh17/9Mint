<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class userController extends Controller
{

    public function logout(){
        auth()->logout();
        return redirect('/test');
    }

    public function register(Request $request){
        $incomingfields = $request->validate([
            'name' => ['required', Rule::unique('users','name')], 
            'email' => ['email', 'required', Rule::unique('users','email')],
            'password' => ['required']
          ]);
            
            $incomingfields['password'] = bcrypt($incomingfields['password']);

           $user = User::create($incomingfields); 
           auth()->login($user);           
            return redirect('/test');
        
    }
    
    public function login(Request $request){
        $incomingfields = $request->validate([
        'loginname' => 'required',
        'loginpassword' => 'required'
        ]);

        if(auth()->attempt(['name' => $incomingfields['loginname'], 'password' => $incomingfields['loginpassword']])){
            $request->session()->regenerate();
        }

        return redirect('/test');
    }

 


}
