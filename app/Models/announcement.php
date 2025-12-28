<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class announcement extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'announcement';
    protected $primaryKey = 'id';
    protected $fillable = ['title', 'description','status'];
}
