<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use App\Models\Patient;
echo "ACTUAL_PATIENT_COUNT: " . Patient::count() . "\n";
foreach (Patient::all() as $p) {
    echo "ID: {$p->id}, Name: {$p->name}\n";
}
