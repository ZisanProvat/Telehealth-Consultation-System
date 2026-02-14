<?php

require __DIR__ . '/vendor/autoload.php';
use Carbon\Carbon;

// Mock data or fetch real doctor
// We can use the first doctor found
$doctor = App\Models\Doctor::first();
echo "Doctor: {$doctor->full_name} (ID: {$doctor->id})\n";
echo "Visiting Days: {$doctor->visiting_days}\n";

$month = 1; // Jan
$year = 2026;
$startDate = Carbon::create($year, $month, 1)->startOfMonth();
$endDate = Carbon::now(); // Up to Jan 11 2026

echo "Checking period: {$startDate->toDateString()} to {$endDate->toDateString()}\n";

// 1. Replicate logic for Assigned Days
$dayMap = [
    'Sunday' => 0,
    'Monday' => 1,
    'Tuesday' => 2,
    'Wednesday' => 3,
    'Thursday' => 4,
    'Friday' => 5,
    'Saturday' => 6,
];
$assignedDayNumbers = [];

// Parse visiting days
$visitingDaysStr = $doctor->visiting_days;
$visitingDays = json_decode($visitingDaysStr, true);
if (!is_array($visitingDays)) {
    $rawStr = trim($visitingDaysStr);
    if (preg_match('/^(\w+)\s+to\s+(\w+)$/i', $rawStr, $matches)) {
        $startDay = ucfirst(strtolower($matches[1]));
        $endDay = ucfirst(strtolower($matches[2]));
        if (isset($dayMap[$startDay]) && isset($dayMap[$endDay])) {
            $startNum = $dayMap[$startDay];
            $endNum = $dayMap[$endDay];
            echo "Parsed Range: $startDay($startNum) to $endDay($endNum)\n";
            if ($startNum <= $endNum) {
                for ($i = $startNum; $i <= $endNum; $i++)
                    $assignedDayNumbers[] = $i;
            } else {
                for ($i = $startNum; $i <= 6; $i++)
                    $assignedDayNumbers[] = $i;
                for ($i = 0; $i <= $endNum; $i++)
                    $assignedDayNumbers[] = $i;
            }
        }
    } else {
        $days = explode(',', $rawStr);
        foreach ($days as $day) {
            $day = ucfirst(strtolower(trim($day)));
            if (isset($dayMap[$day]))
                $assignedDayNumbers[] = $dayMap[$day];
        }
    }
} else {
    foreach ($visitingDays as $day) {
        if (isset($dayMap[$day]))
            $assignedDayNumbers[] = $dayMap[$day];
    }
}
echo "Assigned Day Numbers: " . implode(',', $assignedDayNumbers) . "\n";

// 2. Iterate days
$current = $startDate->copy();
$assignedCount = 0;
while ($current->lte($endDate)) {
    $isAssigned = in_array($current->dayOfWeek, $assignedDayNumbers);
    $dateStr = $current->toDateString();

    // Check appointments
    $hasAppt = App\Models\Appointment::where('doctor_id', $doctor->id)
        ->whereDate('appointment_date', $dateStr)
        ->exists();

    $status = $isAssigned ? "ASSIGNED" : "OFF";
    $apptStatus = $hasAppt ? "HAS_APPT" : "NO_APPT";

    $isAbsent = ($isAssigned && !$hasAppt);
    $marker = $isAbsent ? " <-- ABSENT" : "";

    echo "$dateStr ({$current->format('l')}): $status | $apptStatus $marker\n";

    if ($isAssigned)
        $assignedCount++;
    $current->addDay();
}

echo "Total Assigned Days: $assignedCount\n";
