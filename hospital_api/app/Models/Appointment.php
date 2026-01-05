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
        'payment_method',
        'payment_status',
        'amount',
        'transaction_id',
        'payment_details',
    ];

    protected $casts = [
        'payment_details' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
