<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class semester extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'semester';
    protected $primaryKey = 'id';
    protected $fillable = ['school_year','semester','status'];
}
