<?php

namespace App\Http\Controllers;
use App\Models\State;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function loadContent($page)
    {
        $validPages = [
            'dashboard', 
            'states','regions','districts','taluks','pincode',
            'group-approvals', 
            'approved-groups', 
            'rejected-groups', 
            'first-category', 
            'second-category',
            'third-category',
            'plan',
            'users',
        ];
//  ,''
        if (!in_array($page, $validPages)) {
            return response()->json(['error' => 'Page not found.'], 404);
        }

        switch ($page) {
            case 'group-approvals':
                return view('admin.group-approvals');
            case 'approved-groups':
                return view('admin.approved-groups');
            case 'rejected-groups':
                return view('admin.rejected-groups');
            case 'pincode':
                $states=State::all();
                return view('admin.pincode',compact('states'));
            case 'districts':
                $states=State::all();
                return view('admin.districts',compact('states'));
            default:
                return view('admin.' . $page);
        }
    }
}

