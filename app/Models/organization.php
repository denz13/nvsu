<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class organization extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'organization';
    protected $primaryKey = 'id';
    protected $fillable = ['organization_name','organization_description','photo', 'status','program_id','college_id'];
}
