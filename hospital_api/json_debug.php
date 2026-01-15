<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;

$startDate = '2026-01-01';
$endDate = '2026-01-31';

$apts = Appointment::whereBetween('appointment_date', [$startDate, $endDate])->get();

$allPatients = $apts->pluck('patient_id')->unique()->values()->toArray();
$nonCancelledApts = $apts->filter(function ($a) {
    $status = strtolower(trim($a->status));
    return !in_array($status, ['cancelled', 'no-show']);
});
$nonCancelledPatients = $nonCancelledApts->pluck('patient_id')->unique()->values()->toArray();

echo json_encode([
    'total_apts' => $apts->count(),
    'unique_patients_all' => $allPatients,
    'count_all' => count($allPatients),
    'unique_patients_non_cancelled' => $nonCancelledPatients,
    'count_non_cancelled' => count($nonCancelledPatients),
    'statuses' => $apts->pluck('status')->unique()->values()->toArray()
], JSON_PRETTY_PRINT);
