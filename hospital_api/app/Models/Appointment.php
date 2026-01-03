<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'doctor_id',
        'doctor_name',
        'patient_id',
        'patient_name',
        'appointment_date',
        'serial_number',
        'day',
        'reason',
        'notes',
        'status',
    ];
}
