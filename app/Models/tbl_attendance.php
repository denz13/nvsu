<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\students;
use App\Models\events;
use App\Models\User;

class tbl_attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tbl_attendance';
    protected $primaryKey = 'id';
    protected $fillable = ['event_id','student_id', 'log_time', 'workstate', 'status','scan_by','user_id'];
    
    protected $casts = [
        'log_time' => 'datetime',
        'workstate' => 'string', // Cast to string since column is VARCHAR
        'user_id' => 'string', // Cast to string since column is VARCHAR
    ];

    public function student()
    {
        return $this->belongsTo(students::class, 'student_id');
    }

    public function event()
    {
        return $this->belongsTo(events::class, 'event_id');
    }

    public function students()
    {
        return $this->belongsTo(students::class, 'student_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
