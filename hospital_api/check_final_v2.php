<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;
use App\Models\Doctor;

$startDate = '2026-01-01';
$endDate = '2026-01-31';
$apts = Appointment::whereBetween('appointment_date', [$startDate, $endDate])->get();

function sumDoctorLevel($apts)
{
    $sum = 0;
    foreach (Doctor::all() as $d) {
        $sum += $apts->where('doctor_id', $d->id)->pluck('patient_id')->unique()->count();
    }
    return $sum;
}

echo "1. Global Unique (Non-Cancelled): " . $apts->filter(fn($a) => !in_array(strtolower(trim($a->status)), ['cancelled', 'no-show']))->pluck('patient_id')->unique()->count() . "\n";
echo "2. Global Unique (All): " . $apts->pluck('patient_id')->unique()->count() . "\n";
echo "3. Sum of Doctor-Level (Non-Cancelled): " . sumDoctorLevel($apts->filter(fn($a) => !in_array(strtolower(trim($a->status)), ['cancelled', 'no-show']))) . "\n";
echo "4. Sum of Doctor-Level (All): " . sumDoctorLevel($apts) . "\n";
echo "5. Total Non-Cancelled Appointments: " . $apts->filter(fn($a) => !in_array(strtolower(trim($a->status)), ['cancelled', 'no-show']))->count() . "\n";
echo "6. Total 'Scheduled' Appointments: " . $apts->filter(fn($a) => strtolower(trim($a->status)) === 'scheduled')->count() . "\n";
