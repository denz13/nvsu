<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\events_assign_participants;
use App\Models\students;

class events_list_of_participants extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'events_list_of_participants';
    protected $primaryKey = 'id';
    protected $fillable = ['events_assign_participants_id','students_id','status'];

    public function events_assign_participants()
    {
        return $this->belongsTo(events_assign_participants::class, 'events_assign_participants_id');
    }

    public function students()
    {
        return $this->belongsTo(students::class, 'students_id');
    }
}
