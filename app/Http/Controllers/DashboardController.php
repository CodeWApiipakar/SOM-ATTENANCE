<?php

namespace App\Http\Controllers;


use App\Models\Punch;
use App\Models\Device;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $recent = Punch::with(['device','employee'])->latest('punch_time')->limit(25)->get();
        $devices = Device::orderBy('name')->get();
        return view('dashboard', compact('recent','devices'));
    }
}
