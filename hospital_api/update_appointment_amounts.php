<?php
/**
 * Script to update existing appointments with doctor fees
 * This will set the amount field for appointments that have NULL amount
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;
use App\Models\Doctor;

echo "=== Updating Existing Appointments with Doctor Fees ===\n\n";

// Get all appointments with NULL amount
$appointmentsWithoutAmount = Appointment::whereNull('amount')->get();

echo "Found {$appointmentsWithoutAmount->count()} appointments without amount set.\n\n";

$updated = 0;
$skipped = 0;

foreach ($appointmentsWithoutAmount as $appointment) {
    $doctor = Doctor::find($appointment->doctor_id);

    if ($doctor && $doctor->fees) {
        $appointment->amount = $doctor->fees;
        $appointment->save();
        $updated++;
        echo "✓ Updated Appointment #{$appointment->id} - Set amount to ৳{$doctor->fees} (Dr. {$doctor->full_name})\n";
    } else {
        $skipped++;
        echo "⚠ Skipped Appointment #{$appointment->id} - Doctor not found or no fee set\n";
    }
}

echo "\n=== Summary ===\n";
echo "Updated: {$updated} appointments\n";
echo "Skipped: {$skipped} appointments\n";
echo "\n✓ Done! Now clear the report cache and regenerate reports.\n";
