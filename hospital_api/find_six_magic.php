<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use App\Models\Appointment;
use App\Models\Doctor;

$apts = Appointment::whereBetween('appointment_date', ['2026-01-01', '2026-01-31'])->get();
function check($a)
{
    if ($a->payment_status === 'paid' && $a->amount > 0)
        return true;
    if (strtolower($a->status) === 'completed' && $a->payment_status === null && $a->amount > 0)
        return true;
    return false;
}

$statuses = ['scheduled', 'completed', 'cancelled', 'no-show', 'pending', 'all'];

echo "--- GLOBAL UNIQUE --- \n";
foreach ($statuses as $s) {
    $filtered = $s === 'all' ? $apts : $apts->filter(fn($a) => strtolower($a->status) === $s);
    echo "$s: " . $filtered->pluck('patient_id')->unique()->count() . "\n";
}

echo "\n--- SUM DOCTOR UNIQUE --- \n";
foreach ($statuses as $s) {
    $sum = 0;
    foreach (Doctor::all() as $d) {
        $dA = $apts->where('doctor_id', $d->id);
        $f = $s === 'all' ? $dA : $dA->filter(fn($a) => strtolower($a->status) === $s);
        $sum += $f->pluck('patient_id')->unique()->count();
    }
    echo "$s: $sum\n";
}

echo "\n--- SUM DOCTOR APPOINTMENT COUNT --- \n";
foreach ($statuses as $s) {
    $sum = 0;
    foreach (Doctor::all() as $d) {
        $dA = $apts->where('doctor_id', $d->id);
        $f = $s === 'all' ? $dA : $dA->filter(fn($a) => strtolower($a->status) === $s);
        $sum += $f->count();
    }
    echo "$s: $sum\n";
}

echo "\n--- REVENUE BASED --- \n";
$sumEarningPatientsAll = 0;
foreach (Doctor::all() as $d) {
    $dA = $apts->where('doctor_id', $d->id);
    $rev = $dA->filter(fn($a) => check($a))->sum('amount');
    if ($rev > 0) {
        $sumEarningPatientsAll += $dA->pluck('patient_id')->unique()->count();
    }
}
echo "Sum-Earning-Doctor-Patients (Any Status): $sumEarningPatientsAll\n";

$sumEarningPatientsComp = 0;
foreach (Doctor::all() as $d) {
    $dA = $apts->where('doctor_id', $d->id);
    $rev = $dA->filter(fn($a) => check($a))->sum('amount');
    if ($rev > 0) {
        $sumEarningPatientsComp += $dA->filter(fn($a) => strtolower($a->status) === 'completed')->pluck('patient_id')->unique()->count();
    }
}
echo "Sum-Earning-Doctor-Patients (Only Completed): $sumEarningPatientsComp\n";

$sumEarningPatientsNonCanc = 0;
foreach (Doctor::all() as $d) {
    $dA = $apts->where('doctor_id', $d->id);
    $rev = $dA->filter(fn($a) => check($a))->sum('amount');
    if ($rev > 0) {
        $sumEarningPatientsNonCanc += $dA->filter(fn($a) => !in_array(strtolower($a->status), ['cancelled', 'no-show']))->pluck('patient_id')->unique()->count();
    }
}
echo "Sum-Earning-Doctor-Patients (Non-Cancelled): $sumEarningPatientsNonCanc\n";
