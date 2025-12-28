<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\semester;
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
}
