<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class system_settings extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'system_settings';
    protected $primaryKey = 'id';
    protected $fillable = ['key','type','description','status'];
}
