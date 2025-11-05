<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\attendance_payments;

class attendance_payments_time_schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'attendance_payments_time_schedule';
    protected $primaryKey = 'id';
    protected $fillable = ['attendance_payments_id','type_of_schedule_pay','log_time','workstate','status'];

    public function attendance_payments()
    {
        return $this->belongsTo(attendance_payments::class, 'attendance_payments_id');
    }
}
