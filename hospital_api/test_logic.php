<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;
use App\Models\Doctor;

$startDate = '2026-01-01';
$endDate = '2026-01-31';

$doctors = Doctor::all();
$sumPatientsForEarningDoctors = 0;

foreach ($doctors as $doctor) {
    $apts = Appointment::where('doctor_id', $doctor->id)
        ->whereBetween('appointment_date', [$startDate, $endDate])
        ->get();

    $revenue = $apts->filter(function ($apt) {
        if ($apt->payment_status === 'paid' && $apt->amount > 0)
            return true;
        if (strtolower($apt->status) === 'completed' && $apt->payment_status === null && $apt->amount > 0)
            return true;
        return false;
    })->sum('amount');

    $patientCount = $apts->filter(function ($apt) {
        $status = strtolower(trim($apt->status));
        return $status !== 'cancelled' && $status !== 'no-show';
    })->pluck('patient_id')->unique()->count();

    if ($revenue > 0) {
        echo "Doctor {$doctor->full_name}: Revenue $revenue, Patients $patientCount\n";
        $sumPatientsForEarningDoctors += $patientCount;
    } else if ($patientCount > 0) {
        echo "Doctor {$doctor->full_name}: Revenue 0, Patients $patientCount (SKIPPED)\n";
    }
}

echo "\nFinal Sum for Earning Doctors: $sumPatientsForEarningDoctors\n";
