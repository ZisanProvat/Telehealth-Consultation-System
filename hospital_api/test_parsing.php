<?php

require __DIR__ . '/vendor/autoload.php';

function calculateDaysAssigned($visitingDaysStr)
{
    if (empty($visitingDaysStr)) {
        return 0;
    }

    $assignedDayNumbers = [];
    $dayMap = [
        'Sunday' => 0,
        'Monday' => 1,
        'Tuesday' => 2,
        'Wednesday' => 3,
        'Thursday' => 4,
        'Friday' => 5,
        'Saturday' => 6,
    ];

    $visitingDays = json_decode($visitingDaysStr, true);

    if (!is_array($visitingDays)) {
        $rawStr = trim($visitingDaysStr);
        if (preg_match('/^(\w+)\s+to\s+(\w+)$/i', $rawStr, $matches)) {
            $startDay = ucfirst(strtolower($matches[1]));
            $endDay = ucfirst(strtolower($matches[2]));
            if (isset($dayMap[$startDay]) && isset($dayMap[$endDay])) {
                $startNum = $dayMap[$startDay];
                $endNum = $dayMap[$endDay];
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
    return count($assignedDayNumbers);
}

echo "Testing 'Sunday to Thursday': " . calculateDaysAssigned("Sunday to Thursday") . " (Expected 5)\n";
echo "Testing 'Monday,Wednesday,Friday': " . calculateDaysAssigned("Monday,Wednesday,Friday") . " (Expected 3)\n";
echo "Testing 'Friday to Monday': " . calculateDaysAssigned("Friday to Monday") . " (Expected 4)\n";
