<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;

echo "=== Backfilling Video Call Links ===\n";

$appointments = Appointment::whereNull('notes')
    ->orWhere('notes', 'not like', '%http%')
    ->get();

$count = 0;

foreach ($appointments as $appointment) {
    // Preserve existing notes if any, append link
    $existingNotes = $appointment->notes ? $appointment->notes . "\n\n" : "";

    // Generate link
    $uniqueRoomId = 'TeleHealth-' . uniqid() . '-' . $appointment->serial_number;
    $link = "https://meet.guifi.net/" . $uniqueRoomId;

    $appointment->notes = $existingNotes . $link;
    $appointment->save();

    echo "Updated Appointment ID: {$appointment->id}\n";
    $count++;
}

echo "\nSuccessfully updated {$count} appointments with video links.\n";
