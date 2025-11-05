<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\students;
use App\Models\events;

class attendance_payments extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'attendance_payments';
    protected $primaryKey = 'id';
    protected $fillable = ['students_id','events_id', 'amount_paid','payment_status', 'waiver_reason','waiver_attachment','waiver_amount', 'status'];

    public function students()
    {
        return $this->belongsTo(students::class, 'students_id');
    }

    public function events()
    {
        return $this->belongsTo(events::class, 'events_id');
    }
}
