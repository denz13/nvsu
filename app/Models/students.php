<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\college;
use App\Models\program;
use App\Models\organization;
use App\Traits\LogsActivity;

class students extends Authenticatable
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'students';
    protected $primaryKey = 'id';
    protected $fillable = ['id_number', 'student_name', 'address', 'year_level', 'college_id', 'program_id', 'organization_id', 'photo','barcode','password','status'];

    /**
     * Get the password for authentication.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id_number';
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
}
