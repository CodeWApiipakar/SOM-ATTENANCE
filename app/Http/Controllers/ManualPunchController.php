<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ManualPunchController extends Controller
{
    //
    public function index(){
        return view('manualPunch');
    }
}
