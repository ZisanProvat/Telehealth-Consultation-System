<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\Schema;
echo "APPOINTMENTS: " . implode(', ', Schema::getColumnListing('appointments')) . "\n";
echo "PATIENTS: " . implode(', ', Schema::getColumnListing('patients')) . "\n";
