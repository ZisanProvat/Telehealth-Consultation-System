<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;
use App\Models\Doctor;

$startDate = '2026-01-01';
$endDate = '2026-01-31';

$appointments = Appointment::whereBetween('appointment_date', [$startDate, $endDate])->get();

echo "Total Appointments in Jan 2026: " . $appointments->count() . "\n";

$uniquePatients = $appointments->pluck('patient_id')->unique()->count();
echo "Unique Patients (System-wide): " . $uniquePatients . "\n";

$doctors = Doctor::all();
$sumOfDoctorUniquePatients = 0;
echo "\nBreakdown by Doctor:\n";
foreach ($doctors as $doctor) {
    $doctorApts = $appointments->where('doctor_id', $doctor->id);
    $count = $doctorApts->pluck('patient_id')->unique()->count();
    $sumOfDoctorUniquePatients += $count;
    if ($count > 0) {
        echo "Doctor ID {$doctor->id} ({$doctor->full_name}): {$count} patients, {$doctorApts->count()} appointments\n";
    }
}

echo "\nSum of Doctor-level Unique Patients: " . $sumOfDoctorUniquePatients . "\n";
