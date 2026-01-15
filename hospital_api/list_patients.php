<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;
use App\Models\Patient;

$startDate = '2026-01-01';
$endDate = '2026-01-31';

$apts = Appointment::whereBetween('appointment_date', [$startDate, $endDate])->get();

echo "ALL UNIQUE PATIENT IDs IN JAN 2026 (Regardless of Status):\n";
$uniqueIds = $apts->pluck('patient_id')->unique();
foreach ($uniqueIds as $id) {
    $patient = Patient::find($id);
    $name = $patient ? $patient->name : "Unknown";
    $statuses = $apts->where('patient_id', $id)->pluck('status')->unique()->toArray();
    echo "- ID $id: $name (Statuses: " . implode(', ', $statuses) . ")\n";
}

echo "\nTotal Unique IDs: " . $uniqueIds->count() . "\n";
