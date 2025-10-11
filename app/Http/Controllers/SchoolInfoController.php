<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SchoolInfoController extends Controller
{
    public function index()
    {
        return view('settings.profile');
    }
}
