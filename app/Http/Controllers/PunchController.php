<?php

namespace App\Http\Controllers;

use App\Models\Punch;
use Illuminate\Http\Request;

class PunchController extends Controller
{
    public function index(Request $r)
    {
        $q = Punch::with(['device','employee'])->orderBy('punch_time','desc');

        if ($r->filled('enroll_id')) $q->where('enroll_id', $r->enroll_id);
        if ($r->filled('device_id')) $q->where('device_id', $r->device_id);
        if ($r->filled('from')) $q->where('punch_time','>=',$r->from);
        if ($r->filled('to')) $q->where('punch_time','<=',$r->to);

        return $q->paginate(50);
    }
}
