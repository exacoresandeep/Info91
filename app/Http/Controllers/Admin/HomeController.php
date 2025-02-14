<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // Import the base controller
use Illuminate\Http\Request;
use DB;
use Auth;

class HomeController extends Controller // Extend the base Controller class
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth'); // This will ensure that all methods require authentication

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Return the view if needed
        return view('admin.home');
    }
}
