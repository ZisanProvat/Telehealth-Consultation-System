<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use App\Models\DoctorMonthlyReport;
$sum = DoctorMonthlyReport::where('month', 1)->where('year', 2026)->sum('total_patients');
echo "CACHE_SUM_PATIENTS: $sum\n";
