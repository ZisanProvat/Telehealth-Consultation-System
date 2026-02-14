<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking Appointments Table Structure ===\n\n";

$columns = DB::select("DESCRIBE appointments");

echo "Columns in appointments table:\n";
foreach ($columns as $column) {
    echo "  - {$column->Field} ({$column->Type}) - Default: {$column->Default}, Null: {$column->Null}\n";
}

echo "\n=== Sample Appointment Data ===\n";
$appointments = DB::select("SELECT id, doctor_id, patient_id, appointment_date, status, amount, payment_status, payment_method FROM appointments LIMIT 5");

foreach ($appointments as $apt) {
    echo "\nAppointment ID: {$apt->id}\n";
    echo "  Doctor: {$apt->doctor_id}, Patient: {$apt->patient_id}\n";
    echo "  Date: {$apt->appointment_date}\n";
    echo "  Status: " . ($apt->status ?? 'NULL') . "\n";
    echo "  Amount: " . ($apt->amount ?? 'NULL') . "\n";
    echo "  Payment Status: " . ($apt->payment_status ?? 'NULL') . "\n";
    echo "  Payment Method: " . ($apt->payment_method ?? 'NULL') . "\n";
}
