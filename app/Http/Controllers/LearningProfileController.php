<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LearningProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function interest()
    {
        return view('learning.interest.index');
    }

    public function aptitude()
    {
        return view('learning.aptitude.index');
    }

    public function multipleIntelligence()
    {
        return view('learning.multiple.index');
    }

    public function thinkingStyle()
    {
        return view('learning.thinking.index');
    }

    public function preferences()
    {
        return view('learning.preferences.index');
    }

    public function holland()
    {
        return view('learning.holland.index');
    }

    public function mbti()
    {
        return view('learning.mbti.index');
    }

    public function report()
    {
        return view('learning.report.index');
    }
}
