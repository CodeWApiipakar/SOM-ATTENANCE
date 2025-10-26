<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'name','serial_number','ip_address','port','model','vendor','push_token','is_active'
    ];

    public function punches() { return $this->hasMany(Punch::class); }
}
