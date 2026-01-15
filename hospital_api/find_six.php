<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;
use App\Models\Doctor;

$startDate = '2026-01-01';
$endDate = '2026-01-31';
$apts = Appointment::whereBetween('appointment_date', [$startDate, $endDate])->get();

function getRev($a)
{
    if ($a->payment_status === 'paid' && $a->amount > 0)
        return true;
    if (strtolower($a->status) === 'completed' && $a->payment_status === null && $a->amount > 0)
        return true;
    return false;
}

echo "--- GLOBAL COUNTS ---\n";
echo "Global Unique (All): " . $apts->pluck('patient_id')->unique()->count() . "\n";
echo "Global Unique (Paid/Earning): " . $apts->filter(fn($a) => getRev($a))->pluck('patient_id')->unique()->count() . "\n";
echo "Global Unique (Completed): " . $apts->filter(fn($a) => strtolower($a->status) === 'completed')->pluck('patient_id')->unique()->count() . "\n";

echo "\n--- DOCTOR-LEVEL SUMS ---\n";
$sumAll = 0;
$sumPaid = 0;
$sumCompleted = 0;
$sumNonCancelled = 0;

foreach (Doctor::all() as $d) {
    $dApts = $apts->where('doctor_id', $d->id);
    if ($dApts->isEmpty())
        continue;

    $pAll = $dApts->pluck('patient_id')->unique()->count();
    $pPaid = $dApts->filter(fn($a) => getRev($a))->pluck('patient_id')->unique()->count();
    $pComp = $dApts->filter(fn($a) => strtolower($a->status) === 'completed')->pluck('patient_id')->unique()->count();
    $pNonC = $dApts->filter(fn($a) => !in_array(strtolower($a->status), ['cancelled', 'no-show']))->pluck('patient_id')->unique()->count();

    $sumAll += $pAll;
    $sumPaid += $pPaid;
    $sumCompleted += $pComp;
    $sumNonCancelled += $pNonC;
}

echo "Sum of Doctor-Level Unique (All): $sumAll\n";
echo "Sum of Doctor-Level Unique (Paid/Earning): $sumPaid\n";
echo "Sum of Doctor-Level Unique (Completed): $sumCompleted\n";
echo "Sum of Doctor-Level Unique (Non-Cancelled): $sumNonCancelled\n";

echo "\n--- TOTAL APPOINTMENTS ---\n";
echo "Total Completed Appointments: " . $apts->filter(fn($a) => strtolower($a->status) === 'completed')->count() . "\n";
echo "Total Paid/Earning Appointments: " . $apts->filter(fn($a) => getRev($a))->count() . "\n";
