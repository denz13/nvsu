<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class semester extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'semester';
    protected $primaryKey = 'id';
    protected $fillable = ['school_year','semester','status'];
}
