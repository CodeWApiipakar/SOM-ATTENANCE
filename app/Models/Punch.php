<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Punch extends Model
{
    protected $fillable = [
        'device_id','employee_id','enroll_id','verify_mode','io_mode',
        'punch_time','work_code','source_uid','raw_payload'
    ];

    protected $casts = [
        'punch_time' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function device(){ return $this->belongsTo(Device::class); }
    public function employee(){ return $this->belongsTo(Employee::class); }
}
