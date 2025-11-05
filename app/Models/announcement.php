<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'announcement';
    protected $primaryKey = 'id';
    protected $fillable = ['title', 'description','status'];
}
