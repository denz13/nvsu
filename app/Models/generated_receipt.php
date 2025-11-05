<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\attendance_payments;

class generated_receipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'generated_receipt';
    protected $primaryKey = 'id';
    protected $fillable = ['attendance_payments_id','official_receipts','status'];

    public function attendance_payments()
    {
        return $this->belongsTo(attendance_payments::class, 'attendance_payments_id');
    }
}
