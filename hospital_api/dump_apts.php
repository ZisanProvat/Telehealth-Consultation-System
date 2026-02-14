<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;

$startDate = '2026-01-01';
$endDate = '2026-01-31';

$apts = Appointment::whereBetween('appointment_date', [$startDate, $endDate])->get();

echo "ID | Doctor | Patient | Status | Date\n";
echo "---|---|---|---|---\n";
foreach ($apts as $apt) {
    echo "{$apt->id} | {$apt->doctor_id} | {$apt->patient_id} | {$apt->status} | {$apt->appointment_date}\n";
}
echo "\nTotal: " . $apts->count() . "\n";
