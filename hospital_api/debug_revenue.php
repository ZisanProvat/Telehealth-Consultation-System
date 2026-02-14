<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;
use Carbon\Carbon;

echo "=== Investigating Revenue Issue ===\n\n";

// Check appointments in January 2026
$startDate = '2026-01-01';
$endDate = '2026-01-31';

$appointments = Appointment::whereBetween('appointment_date', [$startDate, $endDate])->get();

echo "Total appointments in January 2026: {$appointments->count()}\n\n";

if ($appointments->count() > 0) {
    echo "Appointment Details:\n";
    echo str_repeat("-", 100) . "\n";
    printf("%-5s %-12s %-15s %-15s %-15s %-10s\n", "ID", "Date", "Status", "Payment Status", "Amount", "Doctor ID");
    echo str_repeat("-", 100) . "\n";

    foreach ($appointments as $apt) {
        printf(
            "%-5s %-12s %-15s %-15s %-15s %-10s\n",
            $apt->id,
            $apt->appointment_date,
            $apt->status ?? 'NULL',
            $apt->payment_status ?? 'NULL',
            $apt->amount ?? 'NULL',
            $apt->doctor_id
        );
    }

    echo "\n";

    // Check completed appointments
    $completed = $appointments->where('status', 'completed');
    echo "Completed appointments: {$completed->count()}\n";

    if ($completed->count() > 0) {
        echo "\nCompleted Appointment Details:\n";
        foreach ($completed as $apt) {
            echo "  ID {$apt->id}: amount={$apt->amount}, payment_status={$apt->payment_status}\n";
        }

        // Check which ones would count for revenue
        $withAmount = $completed->filter(function ($apt) {
            return $apt->amount > 0;
        });
        echo "\nCompleted with amount > 0: {$withAmount->count()}\n";

        $withPaidOrNull = $completed->filter(function ($apt) {
            return ($apt->payment_status === 'paid' || $apt->payment_status === null) && $apt->amount > 0;
        });
        echo "Completed with (paid OR null) AND amount > 0: {$withPaidOrNull->count()}\n";
        echo "Total revenue: " . $withPaidOrNull->sum('amount') . "\n";
    }
}

// Check the appointments table structure
echo "\n=== Checking Appointments Table Columns ===\n";
$sample = Appointment::first();
if ($sample) {
    echo "Available columns:\n";
    foreach ($sample->getAttributes() as $key => $value) {
        echo "  - {$key}: " . ($value ?? 'NULL') . "\n";
    }
}
