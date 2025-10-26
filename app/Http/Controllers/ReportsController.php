<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportsController extends Controller
{
    //

    public function generationReportIndex(){
        return view('generationReport');
    }


    public function summerReportIndex(){
        return view('summeryReport');
    }

    public function manageReportIndex(){
        return view('manageReport');
    }
}
