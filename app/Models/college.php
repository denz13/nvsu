<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\program;
use App\Traits\LogsActivity;

class college extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'college';
    protected $primaryKey = 'id';
    protected $fillable = ['college_name','photo', 'status'];

    public function programs()
    {
        return $this->hasMany(program::class, 'college_id');
    }
}
