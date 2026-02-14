<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;

echo "=== DIAGNOSTIC: Listing Appointment Links ===\n";

$appointments = Appointment::whereNotNull('notes')->get();

foreach ($appointments as $apt) {
    if (strpos($apt->notes, 'http') !== false) {
        echo "[ID: {$apt->id}] [Serial: {$apt->serial_number}] Notes: {$apt->notes}\n";
    }
}

echo "=== END DIAGNOSTIC ===\n";
