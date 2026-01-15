<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use App\Models\Appointment;
$apts = Appointment::whereMonth('appointment_date', 1)->get();
echo "Unique IDs: " . $apts->pluck('patient_id')->unique()->count() . "\n";
echo "Unique Names: " . $apts->pluck('patient_name')->unique()->count() . "\n";
foreach ($apts->pluck('patient_name')->unique() as $name) {
    echo "- $name\n";
}
echo "\nUnique Doctor-Patient pairs: " . $apts->map(fn($a) => $a->doctor_id . '-' . $a->patient_id)->unique()->count() . "\n";
echo "Unique Doctor-PatientName pairs: " . $apts->map(fn($a) => $a->doctor_id . '-' . $a->patient_name)->unique()->count() . "\n";
