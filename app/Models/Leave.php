<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    //

    protected $fillable = [
        'name','serial_number','ip_address','port','model','vendor','push_token','is_active'
    ];
    
}
