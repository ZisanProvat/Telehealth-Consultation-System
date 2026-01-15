<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use App\Models\Appointment;
use App\Models\Doctor;
$apts = Appointment::whereBetween('appointment_date', ['2026-01-01', '2026-01-31'])->get();
function getRev($a)
{
    if ($a->payment_status === 'paid' && $a->amount > 0)
        return true;
    if (strtolower($a->status) === 'completed' && $a->payment_status === null && $a->amount > 0)
        return true;
    return false;
}
$sumPaid = 0;
$sumComp = 0;
$sumPaidComp = 0;
foreach (Doctor::all() as $d) {
    $dA = $apts->where('doctor_id', $d->id);
    if ($dA->isEmpty())
        continue;
    $sumPaid += $dA->filter(fn($a) => getRev($a))->pluck('patient_id')->unique()->count();
    $sumComp += $dA->filter(fn($a) => strtolower($a->status) === 'completed')->pluck('patient_id')->unique()->count();
    $sumPaidComp += $dA->filter(fn($a) => getRev($a) && strtolower($a->status) === 'completed')->pluck('patient_id')->unique()->count();
}
echo "Sum-Doctor-Paid: $sumPaid\n";
echo "Sum-Doctor-Completed: $sumComp\n";
echo "Sum-Doctor-Paid-AND-Completed: $sumPaidComp\n";
echo "Global-Unique-Paid: " . $apts->filter(fn($a) => getRev($a))->pluck('patient_id')->unique()->count() . "\n";
echo "Global-Unique-Completed: " . $apts->filter(fn($a) => strtolower($a->status) === 'completed')->pluck('patient_id')->unique()->count() . "\n";
echo "Sum-Doctor-All: " . Appointment::whereBetween('appointment_date', ['2026-01-01', '2026-01-31'])->get()->groupBy('doctor_id')->map(fn($g) => $g->pluck('patient_id')->unique()->count())->sum() . "\n";
