<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Redirect;
use Session;
use Hash;
use Auth;

class LogincheckController extends Controller
{
    /*****************************************
       Date        : 01/03/2024
       Description :  Login for Submission
    ******************************************/
    public function login(Request $req)
    {
        // return request()->all();
        
        $validatedData = $req->validate([
          'username' => 'required|email',
          'password' => 'required',
        ], 
        [
          'username.required' => 'Please enter the username.',
          'username.email' => 'Please enter a valid email address.',
          'password.required' => 'Please enter the password.',
        ]);

        $credentials = $req->only('username', 'password');
        $check_exist = User::where('email', $req->username)->exists();
        $user = User::where('email', $credentials['username'])->first();

        if (!$check_exist) {
            return Redirect::back()->withErrors(['msg' => 'Incorrect username.']);
        } 
        elseif (!Hash::check($credentials['password'], $user->password)) {
            return Redirect::back()->withErrors(['msg' => 'Incorrect password.']);
        } 
        elseif (Auth::attempt(['email' => $credentials['username'], 'password' => $credentials['password']])) {
            Session::put(['Contact_No' => $user->phone_number, 'Login_User_ID' => $user->email]);
            // dd("Authenticated", Auth::user());
            return redirect()->route('admin.home');
        } 
        else {
            return Redirect::back()->withErrors(['msg' => 'Invalid credentials. Please try again.']);
        }
    }
}
