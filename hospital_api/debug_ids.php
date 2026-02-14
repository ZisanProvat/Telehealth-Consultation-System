<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;

$payments = Appointment::whereNotNull('payment_status')
    ->orderBy('created_at', 'desc')
    ->get()
    ->map(function ($appointment) {
        $doctor = Doctor::find($appointment->doctor_id);
        $patient = Patient::find($appointment->patient_id);
        return [
            'id' => $appointment->id,
            'transaction_id' => $appointment->transaction_id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'patient_name' => $patient ? $patient->name : 'Unknown',
            'doctor_name' => $doctor ? $doctor->full_name : 'Unknown',
            'amount' => $appointment->amount,
            'payment_method' => $appointment->payment_method,
            'payment_status' => $appointment->payment_status,
            'appointment_date' => $appointment->appointment_date,
            'payment_date' => $appointment->created_at,
        ];
    });

file_put_contents('debug_json.json', json_encode($payments, JSON_PRETTY_PRINT));
echo "Saved to debug_json.json\n";
