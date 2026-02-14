<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use App\Models\Appointment;
$all = Appointment::all();
echo "ALL APPOINTMENTS:\n";
foreach ($all as $a) {
    echo "ID: {$a->id}, Patient: {$a->patient_id}, Date: {$a->appointment_date}, Status: {$a->status}\n";
}
echo "Total: " . $all->count() . "\n";
echo "Global Unique Patients (All Time): " . $all->pluck('patient_id')->unique()->count() . "\n";
