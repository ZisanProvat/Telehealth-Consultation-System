<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $table = 'doctors';
    
    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'phone',
        'designation',
        'specialization',
        'qualification',
        'experience',
        'bmdc_no',
        'visiting_hours',
        'description',
        'photo',
        'password'
    ];
}

