<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class college extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'college';
    protected $primaryKey = 'id';
    protected $fillable = ['college_name','photo', 'status'];
}
