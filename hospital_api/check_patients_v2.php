<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;

$startDate = '2026-01-01';
$endDate = '2026-01-31';

$appointments = Appointment::whereBetween('appointment_date', [$startDate, $endDate])->get();

echo "Total Raw Appointments: " . $appointments->count() . "\n";
echo "Statuses:\n";
foreach ($appointments->groupBy('status') as $status => $group) {
    echo "- $status: " . $group->count() . "\n";
}

$uniquePatientsAll = $appointments->pluck('patient_id')->unique()->count();
echo "Unique Patients (Any Status): " . $uniquePatientsAll . "\n";

$nonCancelled = $appointments->filter(function ($a) {
    return !in_array(strtolower($a->status), ['cancelled', 'no-show']);
});
echo "Non-Cancelled Appointments: " . $nonCancelled->count() . "\n";
echo "Unique Patients (Non-Cancelled): " . $nonCancelled->pluck('patient_id')->unique()->count() . "\n";
