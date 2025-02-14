<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Cookie;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

class LoginController 
{
   public function login(){
    return view('admin.login');
   }

   public function logout(Request $request)
   {
       Auth::logout(); 
       Cookie::queue(Cookie::forget('selectedLink'));
       $request->session()->invalidate();
       $request->session()->regenerateToken();
       return redirect()->route('login')->with('success', 'Successfully logged out!');
   }
}
