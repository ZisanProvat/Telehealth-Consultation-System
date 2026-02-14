<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;

echo "=== Checking Payment Status ===\n\n";

$appointments = Appointment::all();

echo "Total appointments: {$appointments->count()}\n\n";

echo "Breakdown by payment_status:\n";
$paid = $appointments->where('payment_status', 'paid')->count();
$pending = $appointments->where('payment_status', 'pending')->count();
$nullStatus = $appointments->whereNull('payment_status')->count();

echo "  - Paid: {$paid}\n";
echo "  - Pending: {$pending}\n";
echo "  - Null: {$nullStatus}\n\n";

echo "Appointments with payment_status = 'paid':\n";
$paidAppointments = $appointments->where('payment_status', 'paid');
foreach ($paidAppointments as $apt) {
    echo "  ID: {$apt->id}, Status: {$apt->status}, Amount: ৳{$apt->amount}, Date: {$apt->appointment_date}\n";
}

$totalRevenue = $paidAppointments->sum('amount');
echo "\nTotal Revenue from Paid Appointments: ৳{$totalRevenue}\n";
