<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\students;
class message extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'message';
    protected $primaryKey = 'id';
    protected $fillable = ['from_id', 'message_content', 'to_id', 'status'];

    /**
     * Get sender - can be User or Student
     */
    public function getSenderAttribute()
    {
        // Try User first
        $user = User::find($this->from_id);
        if ($user) {
            return $user;
        }
        
        // Try Student
        $student = students::find($this->from_id);
        if ($student) {
            return $student;
        }
        
        return null;
    }
    
    /**
     * Get receiver - can be User or Student
     */
    public function getReceiverAttribute()
    {
        // Try User first
        $user = User::find($this->to_id);
        if ($user) {
            return $user;
        }
        
        // Try Student
        $student = students::find($this->to_id);
        if ($student) {
            return $student;
        }
        
        return null;
    }
    
    // Keep old relationships for backward compatibility but make them nullable
    public function sender()
    {
        return $this->belongsTo(students::class, 'from_id')->withDefault();
    }

    public function receiver()
    {
        return $this->belongsTo(students::class, 'to_id')->withDefault();
    }
    
    public function from()
    {
        return $this->belongsTo(User::class, 'from_id')->withDefault();
    }

    public function to()
    {
        return $this->belongsTo(User::class, 'to_id')->withDefault();
    }
}
