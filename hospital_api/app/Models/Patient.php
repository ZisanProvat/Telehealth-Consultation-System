<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Patient extends Model
{
    use HasFactory;

    // ✅ Define all fillable fields
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'age',
        'height',
        'weight',
        'blood_group',
        'previous_record',
        'uploaded_record',
        'photo',
        'password',
    ];

    // Optional: Add mutator for password hashing

}


