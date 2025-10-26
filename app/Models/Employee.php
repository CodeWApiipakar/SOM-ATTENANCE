<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';
    protected $fillable = ['enroll_id', 'emp_code', 'name', 'department'];

    public function punches()
    {
        return $this->hasMany(Punch::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class,   'companyId');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'departmentId');
    }
    public function section()
    {
        return $this->belongsTo(Section::class,   'sectionId');
    }
}
