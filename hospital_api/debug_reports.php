<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Doctor;
use App\Models\Appointment;
use Carbon\Carbon;

echo "=== Debugging Report Calculation ===\n\n";

// Check first doctor
$doctor = Doctor::first();
if (!$doctor) {
    echo "No doctors found!\n";
    exit;
}

echo "Doctor: {$doctor->full_name} (ID: {$doctor->id})\n";
echo "Visiting Days: {$doctor->visiting_days}\n\n";

// Check appointments for January 2026
$startDate = '2026-01-01';
$endDate = '2026-01-31';

$appointments = Appointment::where('doctor_id', $doctor->id)
    ->whereBetween('appointment_date', [$startDate, $endDate])
    ->get();

echo "Appointments in January 2026: {$appointments->count()}\n\n";

if ($appointments->count() > 0) {
    echo "Sample appointments:\n";
    foreach ($appointments->take(5) as $apt) {
        echo "  - ID: {$apt->id}, Date: {$apt->appointment_date}, Status: {$apt->status}, ";
        echo "Payment Status: {$apt->payment_status}, Amount: {$apt->amount}\n";
    }
    echo "\n";

    // Count by status
    echo "By Status:\n";
    echo "  - Completed: " . $appointments->where('status', 'completed')->count() . "\n";
    echo "  - Scheduled: " . $appointments->where('status', 'scheduled')->count() . "\n";
    echo "  - Cancelled: " . $appointments->where('status', 'cancelled')->count() . "\n";
    echo "\n";

    // Check payment status
    echo "Payment Status:\n";
    $paid = $appointments->where('payment_status', 'paid')->count();
    $pending = $appointments->where('payment_status', 'pending')->count();
    $unpaid = $appointments->where('payment_status', null)->count();
    echo "  - Paid: {$paid}\n";
    echo "  - Pending: {$pending}\n";
    echo "  - Null/Unpaid: {$unpaid}\n";
}

// Check all appointments regardless of month
echo "\n=== All Appointments for this doctor ===\n";
$allApts = Appointment::where('doctor_id', $doctor->id)->get();
echo "Total: {$allApts->count()}\n";
if ($allApts->count() > 0) {
    echo "Date range: " . $allApts->min('appointment_date') . " to " . $allApts->max('appointment_date') . "\n";
}
