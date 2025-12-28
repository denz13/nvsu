<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class module extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'module';
    protected $primaryKey = 'id';
    protected $fillable = ['module', 'status'];
}
