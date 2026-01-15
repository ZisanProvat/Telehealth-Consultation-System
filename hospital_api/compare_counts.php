<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;
use App\Models\Doctor;

$startDate = '2026-01-01';
$endDate = '2026-01-31';

$apts = Appointment::whereBetween('appointment_date', [$startDate, $endDate])->get();
$nonCancelled = $apts->filter(function ($a) {
    $status = strtolower(trim($a->status));
    return !in_array($status, ['cancelled', 'no-show']);
});

echo "Total Non-Cancelled Appointments: " . $nonCancelled->count() . "\n";
echo "Global Unique Patients (Non-Cancelled): " . $nonCancelled->pluck('patient_id')->unique()->count() . "\n";

$sumOfDoctorPatients = 0;
foreach (Doctor::all() as $doctor) {
    $count = $nonCancelled->where('doctor_id', $doctor->id)->pluck('patient_id')->unique()->count();
    $sumOfDoctorPatients += $count;
}

echo "Sum of Doctor-level Unique Patients (Non-Cancelled): " . $sumOfDoctorPatients . "\n";
echo "Total Unique Patients (Any Status): " . $apts->pluck('patient_id')->unique()->count() . "\n";
