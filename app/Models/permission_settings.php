<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\students;
class permission_settings extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'permission_settings';
    protected $primaryKey = 'id';
    protected $fillable = ['users_id', 'students_id', 'status'];

    public function users()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function students()
    {
        return $this->belongsTo(students::class, 'students_id');
    }

    public function modules()
    {
        return $this->belongsToMany(module::class, 'permission_settings_list', 'permission_settings_id', 'module_id')
            ->wherePivot('status', 'active')
            ->withTimestamps();
    }

}
