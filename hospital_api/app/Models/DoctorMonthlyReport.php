<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorMonthlyReport extends Model
{
    use HasFactory;

    protected $table = 'doctor_monthly_reports';

    protected $fillable = [
        'doctor_id',
        'month',
        'year',
        'days_assigned',
        'days_absent',
        'total_patients',
        'total_revenue',
        'completed_appointments',
        'scheduled_appointments',
        'cancelled_appointments',
        'no_show_appointments',
        'average_patients_per_day',
        'completion_rate',
    ];

    protected $casts = [
        'total_revenue' => 'decimal:2',
        'average_patients_per_day' => 'decimal:2',
        'completion_rate' => 'decimal:2',
    ];

    /**
     * Get the doctor that owns the report.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}
