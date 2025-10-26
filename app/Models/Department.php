<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'department';
    protected $fillable = [
        'name','code','companyId'
    ];
    public function company()   { return $this->belongsTo(Company::class,   'companyId'); }
}
