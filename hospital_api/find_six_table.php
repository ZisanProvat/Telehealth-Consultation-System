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

echo "Doctor List Breakdown (Jan 2026):\n";
echo "ID | Name | All | Non-Canc | Paid | Completed | Revenue\n";
echo "---|---|---|---|---|---|---\n";

foreach (Doctor::all() as $d) {
    $dA = $apts->where('doctor_id', $d->id);
    if ($dA->isEmpty())
        continue;

    $pAll = $dA->pluck('patient_id')->unique()->count();
    $pNonC = $dA->filter(fn($a) => !in_array(strtolower($a->status), ['cancelled', 'no-show']))->pluck('patient_id')->unique()->count();
    $pPaid = $dA->filter(fn($a) => getRev($a))->pluck('patient_id')->unique()->count();
    $pComp = $dA->filter(fn($a) => strtolower($a->status) === 'completed')->pluck('patient_id')->unique()->count();
    $rev = $dA->filter(fn($a) => getRev($a))->sum('amount');

    echo "{$d->id} | {$d->full_name} | $pAll | $pNonC | $pPaid | $pComp | $rev\n";
}
