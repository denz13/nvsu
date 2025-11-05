<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\events;
use App\Models\college;
use App\Models\program;
use App\Models\organization;
use App\Models\semester;

class events_assign_participants extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'events_assign_participants';
    protected $primaryKey = 'id';
    protected $fillable = ['events_id','college_id','program_id','organization_id','semester_id','status'];

    public function events()
    {
        return $this->belongsTo(events::class, 'events_id');
    }

    public function college()
    {
        return $this->belongsTo(college::class, 'college_id');
    }

    public function program()
    {
        return $this->belongsTo(program::class, 'program_id');
    }

    public function organization()
    {
        return $this->belongsTo(organization::class, 'organization_id');
    }

    public function semester()
    {
        return $this->belongsTo(semester::class, 'semester_id');
    }
}
