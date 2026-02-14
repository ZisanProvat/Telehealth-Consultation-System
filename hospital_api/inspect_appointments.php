<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;

$startDate = '2026-01-01';
$endDate = '2026-01-31';

$appointments = Appointment::whereBetween('appointment_date', [$startDate, $endDate])->get();

echo "DEBUG: Total Appointments Found: " . $appointments->count() . "\n";

$statusCounts = [];
$patientIdsByStatus = [];

foreach ($appointments as $apt) {
    $status = strtolower(trim($apt->status));
    $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
    if (!isset($patientIdsByStatus[$status])) {
        $patientIdsByStatus[$status] = [];
    }
    $patientIdsByStatus[$status][] = $apt->patient_id;
}

echo "\nDEBUG Status Breakdown:\n";
foreach ($statusCounts as $status => $count) {
    $uniquePatientsForStatus = count(array_unique($patientIdsByStatus[$status]));
    echo "- '$status': $count appointments, $uniquePatientsForStatus unique patients\n";
}

$nonCancelledPatients = [];
foreach ($patientIdsByStatus as $status => $ids) {
    if ($status !== 'cancelled' && $status !== 'no-show') {
        $nonCancelledPatients = array_merge($nonCancelledPatients, $ids);
    }
}
$finalUnique = count(array_unique($nonCancelledPatients));

echo "\nDEBUG Results:\n";
echo "Unique Patients (Total): " . $appointments->pluck('patient_id')->unique()->count() . "\n";
echo "Unique Patients (Non-Cancelled/No-show): " . $finalUnique . "\n";
echo "Direct Pluck Unique (Non-Cancelled): " . $appointments->filter(fn($a) => !in_array(strtolower(trim($a->status)), ['cancelled', 'no-show']))->pluck('patient_id')->unique()->count() . "\n";
