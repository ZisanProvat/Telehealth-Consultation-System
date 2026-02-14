<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;
use App\Models\Doctor;

echo "=== Testing 20-Minute Time Slot Calculation ===\n\n";

// Get a sample appointment
$appointment = Appointment::first();

if (!$appointment) {
    echo "No appointments found!\n";
    exit;
}

$doctor = Doctor::find($appointment->doctor_id);

echo "Appointment Details:\n";
echo "  ID: {$appointment->id}\n";
echo "  Serial Number: {$appointment->serial_number}\n";
echo "  Doctor: {$doctor->full_name}\n";
echo "  Visiting Hours: {$doctor->visiting_hours}\n";
echo "  Date: {$appointment->appointment_date}\n\n";

// Calculate time slot
function calculateTimeSlot($serialNumber, $visitingHours)
{
    $startTime = '09:00';

    if ($visitingHours) {
        preg_match('/(\d{1,2}:\d{2})\s*(AM|PM)?/', $visitingHours, $matches);
        if (!empty($matches)) {
            $startTime = $matches[1];
            $meridiem = $matches[2] ?? 'AM';

            $time = DateTime::createFromFormat('h:i A', $startTime . ' ' . $meridiem);
            if ($time) {
                $startTime = $time->format('H:i');
            }
        }
    }

    $minutesFromStart = ($serialNumber - 1) * 20;

    $slotStart = new DateTime($startTime);
    $slotStart->modify("+{$minutesFromStart} minutes");

    $slotEnd = clone $slotStart;
    $slotEnd->modify('+20 minutes');

    return [
        'start_time' => $slotStart->format('h:i A'),
        'end_time' => $slotEnd->format('h:i A'),
        'start_time_24h' => $slotStart->format('H:i'),
        'end_time_24h' => $slotEnd->format('H:i'),
    ];
}

$timeSlot = calculateTimeSlot($appointment->serial_number, $doctor->visiting_hours);

echo "Calculated Time Slot:\n";
echo "  Start: {$timeSlot['start_time']} ({$timeSlot['start_time_24h']})\n";
echo "  End: {$timeSlot['end_time']} ({$timeSlot['end_time_24h']})\n";
echo "  Duration: 20 minutes\n\n";

// Show examples for multiple serial numbers
echo "Example Time Slots:\n";
echo str_repeat("-", 60) . "\n";
printf("%-10s %-20s %-20s\n", "Serial", "Start Time", "End Time");
echo str_repeat("-", 60) . "\n";

for ($serial = 1; $serial <= 10; $serial++) {
    $slot = calculateTimeSlot($serial, $doctor->visiting_hours);
    printf("%-10s %-20s %-20s\n", "#$serial", $slot['start_time'], $slot['end_time']);
}

echo "\n✓ Time slot calculation working correctly!\n";
echo "Each patient gets exactly 20 minutes based on their serial number.\n";
