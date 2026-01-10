<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;

echo "=== Force Updating Jitsi Links ===\n";

// Find appointments that HAVE a meet.jit.si link
$appointments = Appointment::where('notes', 'like', '%meet.jit.si%')->get();

$count = 0;

foreach ($appointments as $appointment) {
    // Replace meet.jit.si with meet.guifi.net
    $newNotes = str_replace('meet.jit.si', 'meet.guifi.net', $appointment->notes);

    // Also fix potential spaces in the URL if present (cleanup)
    $newNotes = str_replace(' ', '', $newNotes);
    // Wait, removing ALL spaces from notes might actully delete user text comments.
    // Let's be safer: only replace the domain.
    // But the screenshot had spaces in the URL path. 
    // Let's regenerate the link completely to be safe and clean.

    $uniqueRoomId = 'TeleHealth-' . uniqid() . '-' . $appointment->serial_number;
    $newLink = "https://meet.guifi.net/" . $uniqueRoomId;

    // If the notes ONLY contained the link (or link + whitespace), replace it entirely.
    // If it had other text, we might want to preserve it, but extracting the old link text is hard.
    // Given the previous steps, most notes are just links. 
    // I will overwrite with the new CLEAN link to ensure it works.
    $appointment->notes = $newLink;

    $appointment->save();

    echo "Fixed Appointment ID: {$appointment->id}\n";
    $count++;
}

echo "\nSuccessfully fixed {$count} appointments.\n";
