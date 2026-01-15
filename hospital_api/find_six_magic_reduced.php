<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use App\Models\Appointment;
use App\Models\Doctor;
$apts = Appointment::whereBetween('appointment_date', ['2026-01-01', '2026-01-31'])->get();
echo "--- SUM DOCTOR UNIQUE ---\n";
foreach (['scheduled', 'completed', 'all'] as $s) {
    $sum = 0;
    foreach (Doctor::all() as $d) {
        $dA = $apts->where('doctor_id', $d->id);
        if ($s === 'all')
            $f = $dA;
        else
            $f = $dA->filter(fn($a) => strtolower(trim($a->status)) === $s);
        $sum += $f->pluck('patient_id')->unique()->count();
    }
    echo "$s: $sum\n";
}
echo "\n--- SUM DOCTOR APTS ---\n";
foreach (['scheduled', 'completed', 'all'] as $s) {
    $sum = 0;
    foreach (Doctor::all() as $d) {
        $dA = $apts->where('doctor_id', $d->id);
        if ($s === 'all')
            $f = $dA;
        else
            $f = $dA->filter(fn($a) => strtolower(trim($a->status)) === $s);
        $sum += $f->count();
    }
    echo "$s: $sum\n";
}
