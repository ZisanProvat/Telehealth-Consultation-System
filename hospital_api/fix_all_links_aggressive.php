<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;

echo "=== AGGRESSIVE LINK RESET ===\n";

$appointments = Appointment::all();
$count = 0;

foreach ($appointments as $appointment) {
    // Generate a fresh unique room ID for every single appointment
    // Reverting to meet.jit.si to enforce "Waiting for Moderator" policy
    $uniqueRoomId = 'TeleHealth-' . uniqid() . '-' . $appointment->serial_number;
    $newLink = "https://meet.jit.si/" . $uniqueRoomId;

    // We will preserve other notes but REPLACE any http link
    $notes = $appointment->notes ?? '';

    if (strpos($notes, 'http') !== false) {
        // If it has a link, replace everything? verify if other notes exist.
        // Assuming primarily links for now or appended links. 
        // Let's just Regex replace the http part.
        $newNotes = preg_replace('/https?:\/\/[^\s]+/', $newLink, $notes);
    } else {
        // No link? Append it.
        $newNotes = trim($notes . " " . $newLink);
    }

    $appointment->notes = $newNotes;
    $appointment->save();
    $count++;
    echo "Reset ID: {$appointment->id} -> $newLink\n";
}

echo "\nRegenerated links for {$count} appointments.\n";
