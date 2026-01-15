<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use App\Models\Appointment;
$d1 = Appointment::whereBetween('appointment_date', ['2026-01-01', '2026-01-31'])->count();
$d2 = Appointment::whereMonth('appointment_date', 1)->whereYear('appointment_date', 2026)->count();
$d3 = Appointment::where('appointment_date', 'like', '2026-01-%')->count();
echo "whereBetween: $d1\n";
echo "whereMonth: $d2\n";
echo "whereLike: $d3\n";

$p1 = Appointment::whereBetween('appointment_date', ['2026-01-01', '2026-01-31'])->pluck('patient_id')->unique()->count();
$p2 = Appointment::whereMonth('appointment_date', 1)->whereYear('appointment_date', 2026)->pluck('patient_id')->unique()->count();
echo "UniqueP1: $p1\n";
echo "UniqueP2: $p2\n";
