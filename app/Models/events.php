<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\semester;
use App\Models\events_assign_participants;
use App\Models\events_list_of_participants;
use App\Traits\LogsActivity;

class events extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'events';
    protected $primaryKey = 'id';
    protected $fillable = ['semester_id','event_name','event_description','start_datetime_morning','end_datetime_morning','start_datetime_afternoon','end_datetime_afternoon','fines','event_schedule_type','status'];

    public function semester()
    {
        return $this->belongsTo(semester::class, 'semester_id');
    }

    public function getParticipantsCountAttribute()
    {
        $assignmentIds = events_assign_participants::where('events_id', $this->id)->pluck('id');
        return events_list_of_participants::whereIn('events_assign_participants_id', $assignmentIds)->count();
    }
}
