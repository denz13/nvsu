<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\college;

class program extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'program';
    protected $primaryKey = 'id';
    protected $fillable = ['college_id', 'program_name', 'photo', 'status'];

    public function college()
    {
        return $this->belongsTo(college::class, 'college_id');
    }

}
