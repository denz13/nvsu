<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class events_lates_deduction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'events_lates_deduction';
    protected $primaryKey = 'id';
    protected $fillable = ['events_id','time_in_morning','time_out_afternoon','time_in_afternoon','time_out_morning','late_penalty','semester_id','status'];

    public function events()
    {
        return $this->belongsTo(events::class, 'events_id');
    }
}
