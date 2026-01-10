<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\DoctorMonthlyReport;

DoctorMonthlyReport::truncate();
echo "✓ Cleared all cached reports. Reports will be regenerated with new logic.\n";
