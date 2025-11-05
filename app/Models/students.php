<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\college;
use App\Models\program;
use App\Models\organization;

class students extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $table = 'students';
    protected $primaryKey = 'id';
    protected $fillable = ['id_number', 'student_name', 'address', 'year_level', 'college_id', 'program_id', 'organization_id', 'photo','barcode','password','status'];

    public function college()
    {
        return $this->belongsTo(college::class, 'college_id');
    }

    public function program()
    {
        return $this->belongsTo(program::class, 'program_id');
    }

    public function organization()
    {
        return $this->belongsTo(organization::class, 'organization_id');
    }
}
