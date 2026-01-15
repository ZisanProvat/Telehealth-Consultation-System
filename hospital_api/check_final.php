<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;

$startDate = '2026-01-01';
$endDate = '2026-01-31';
$apts = Appointment::whereBetween('appointment_date', [$startDate, $endDate])->get();

foreach (['scheduled', 'completed', 'cancelled', 'no-show'] as $status) {
    $count = $apts->filter(fn($a) => strtolower(trim($a->status)) === $status)->pluck('patient_id')->unique()->count();
    echo "$status unique patients: $count\n";
}

$nonCancelledCount = $apts->filter(fn($a) => !in_array(strtolower(trim($a->status)), ['cancelled', 'no-show']))->pluck('patient_id')->unique()->count();
echo "Non-cancelled unique patients: $nonCancelledCount\n";

$anyStatusCount = $apts->pluck('patient_id')->unique()->count();
echo "Any status unique patients: $anyStatusCount\n";
