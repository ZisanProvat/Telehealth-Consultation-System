<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;
use App\Models\Patient;

echo "Total Patients in Database: " . Patient::count() . "\n";

$startDate = '2026-01-01';
$endDate = '2026-01-31';
$apts = Appointment::whereBetween('appointment_date', [$startDate, $endDate])->get();

echo "Appointments in Jan 2026: " . $apts->count() . "\n";
echo "Unique Patient IDs in Jan 2026: " . $apts->pluck('patient_id')->unique()->count() . "\n";
foreach ($apts->pluck('patient_id')->unique() as $id) {
    $p = Patient::find($id);
    echo "- Patient $id: " . ($p ? $p->name : "NOT FOUND") . "\n";
}
