<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\DoctorMonthlyReport;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DoctorReportController extends Controller
{
    /**
     * Generate monthly report for a specific doctor
     */
    public function generateMonthlyReport(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        $doctorId = $request->doctor_id;
        $month = $request->month;
        $year = $request->year;

        $report = $this->calculateMonthlyReport($doctorId, $month, $year);

        // Save or update the report
        DoctorMonthlyReport::updateOrCreate(
            [
                'doctor_id' => $doctorId,
                'month' => $month,
                'year' => $year,
            ],
            $report
        );

        return response()->json([
            'success' => true,
            'message' => 'Report generated successfully',
            'data' => $report
        ]);
    }

    /**
     * Get monthly report for a specific doctor
     */
    public function getMonthlyReport(Request $request, $doctorId)
    {
        $month = $request->query('month', date('n'));
        $year = $request->query('year', date('Y'));

        // Check if report is for current month/year
        $isCurrentMonth = ($month == date('n') && $year == date('Y'));

        // Try to get cached report first
        $report = DoctorMonthlyReport::where('doctor_id', $doctorId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        // If not found OR if it is the current month (needs live update), generate it
        if (!$report || $isCurrentMonth) {
            $reportData = $this->calculateMonthlyReport($doctorId, $month, $year);
            $report = DoctorMonthlyReport::updateOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'month' => $month,
                    'year' => $year,
                ],
                $reportData
            );
        }

        // Get doctor info
        $doctor = Doctor::find($doctorId);

        return response()->json([
            'success' => true,
            'data' => [
                'report' => $report,
                'doctor' => [
                    'id' => $doctor->id,
                    'full_name' => $doctor->full_name,
                    'specialization' => $doctor->specialization,
                    'designation' => $doctor->designation,
                ]
            ]
        ]);
    }

    /**
     * Get reports for all doctors for a specific month
     */
    public function getAllDoctorsReport(Request $request)
    {
        $month = $request->query('month');
        $year = $request->query('year');
        $startDateStr = $request->query('start_date');
        $endDateStr = $request->query('end_date');

        if ($startDateStr && $endDateStr) {
            $startDate = Carbon::parse($startDateStr)->startOfDay();
            $endDate = Carbon::parse($endDateStr)->endOfDay();
        } else {
            $month = $month ?: date('n');
            $year = $year ?: date('Y');
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        }

        $doctors = Doctor::all();
        $reports = [];

        foreach ($doctors as $doctor) {
            if ($startDateStr && $endDateStr) {
                // For custom range, calculate live
                $report = $this->calculateRangeReport($doctor->id, $startDate, $endDate);
            } else {
                // For monthly range, use cache logic
                $report = DoctorMonthlyReport::where('doctor_id', $doctor->id)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->first();

                $isCurrentMonth = ($month == date('n') && $year == date('Y'));
                if (!$report || $isCurrentMonth) {
                    $reportData = $this->calculateRangeReport($doctor->id, $startDate, $endDate);
                    $report = DoctorMonthlyReport::updateOrCreate(
                        [
                            'doctor_id' => $doctor->id,
                            'month' => $month,
                            'year' => $year,
                        ],
                        $reportData
                    );
                }
            }

            $reports[] = [
                'doctor' => [
                    'id' => $doctor->id,
                    'full_name' => $doctor->full_name,
                    'specialization' => $doctor->specialization,
                    'designation' => $doctor->designation,
                ],
                'report' => $report
            ];
        }

        // Calculate global summary stats for the period
        $allAppointments = Appointment::whereBetween('appointment_date', [$startDate->toDateString(), $endDate->toDateString()])->get();

        $totalSystemRevenue = $allAppointments->filter(function ($apt) {
            if ($apt->payment_status === 'paid' && $apt->amount > 0)
                return true;
            if (strtolower($apt->status) === 'completed' && $apt->payment_status === null && $apt->amount > 0)
                return true;
            return false;
        })->sum('amount');

        $totalSystemAppointments = $allAppointments->count();
        $totalSystemCompleted = $allAppointments->filter(fn($a) => strtolower($a->status) === 'completed')->count();
        $systemCompletionRate = $totalSystemAppointments > 0 ? round(($totalSystemCompleted / $totalSystemAppointments) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'data' => $reports,
            'summary' => [
                'total_revenue' => $totalSystemRevenue,
                'total_patients' => $allAppointments->filter(function ($apt) {
                    $status = strtolower($apt->status);
                    return $status !== 'cancelled' && $status !== 'no-show';
                })->pluck('patient_id')->unique()->count(),
                'total_appointments' => $totalSystemAppointments,
                'completion_rate' => $systemCompletionRate,
                'active_doctors' => $allAppointments->pluck('doctor_id')->unique()->count()
            ],
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'is_custom' => (bool) ($startDateStr && $endDateStr)
            ]
        ]);
    }

    /**
     * Force sync/regenerate reports for all doctors for a specific month
     */
    public function syncReports(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        $doctors = Doctor::all();
        $syncedCount = 0;

        foreach ($doctors as $doctor) {
            $reportData = $this->calculateMonthlyReport($doctor->id, $month, $year);
            DoctorMonthlyReport::updateOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'month' => $month,
                    'year' => $year,
                ],
                $reportData
            );
            $syncedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully synced reports for $syncedCount doctors for $month/$year"
        ]);
    }

    /**
     * Get yearly report for a specific doctor (all 12 months)
     */
    public function getYearlyReport($doctorId, Request $request)
    {
        $year = $request->query('year', date('Y'));
        $reports = [];
        $currentMonth = date('n');
        $currentYear = date('Y');

        for ($month = 1; $month <= 12; $month++) {
            $isCurrentMonth = ($month == $currentMonth && $year == $currentYear);

            $report = DoctorMonthlyReport::where('doctor_id', $doctorId)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if (!$report || $isCurrentMonth) {
                // Determine if we should generate report for this month
                // Only generate if month is past or current
                if ($year < $currentYear || ($year == $currentYear && $month <= $currentMonth)) {
                    $reportData = $this->calculateMonthlyReport($doctorId, $month, $year);
                    $report = DoctorMonthlyReport::updateOrCreate(
                        [
                            'doctor_id' => $doctorId,
                            'month' => $month,
                            'year' => $year,
                        ],
                        $reportData
                    );
                } else {
                    // Future months, possibly return empty or placeholder
                    $report = null;
                }
            }

            if ($report) {
                $reports[] = $report;
            }
        }

        $doctor = Doctor::find($doctorId);

        return response()->json([
            'success' => true,
            'data' => [
                'doctor' => [
                    'id' => $doctor->id,
                    'full_name' => $doctor->full_name,
                    'specialization' => $doctor->specialization,
                ],
                'reports' => $reports
            ]
        ]);
    }

    /**
     * Calculate monthly report metrics for a doctor
     */
    /**
     * Calculate monthly report metrics for a doctor
     */
    private function calculateMonthlyReport($doctorId, $month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        return $this->calculateRangeReport($doctorId, $startDate, $endDate);
    }

    /**
     * Calculate report metrics for a doctor over any date range
     */
    private function calculateRangeReport($doctorId, $startDate, $endDate)
    {
        $doctor = Doctor::findOrFail($doctorId);

        // Define attendance calculation limit (To Date if range is in future)
        $attendanceEndDate = $endDate->isFuture() ? Carbon::now() : $endDate;

        // Get all appointments for this doctor in this range
        $appointments = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('appointment_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        // Group appointments by date for daily analysis
        $appointmentsByDate = $appointments->groupBy(function ($item) {
            return Carbon::parse($item->appointment_date)->format('Y-m-d');
        });

        // Count appointments by status
        $completedAppointments = $appointments->filter(function ($apt) {
            return strtolower($apt->status) === 'completed';
        })->count();

        $totalAppointmentsScheduled = $appointments->count();
        $cancelledAppointments = $appointments->filter(fn($a) => strtolower($a->status) === 'cancelled')->count();
        $noShowAppointments = $appointments->filter(fn($a) => strtolower($a->status) === 'no-show')->count();

        // Calculate Revenue
        $totalRevenue = $appointments->filter(function ($apt) {
            if ($apt->payment_status === 'paid' && $apt->amount > 0)
                return true;
            if (strtolower($apt->status) === 'completed' && $apt->payment_status === null && $apt->amount > 0)
                return true;
            return false;
        })->sum('amount');

        // Attendance Logic
        $daysAssignedCount = 0;
        $daysAbsentCount = 0;
        $current = $startDate->copy();

        $dayMap = ['Sunday' => 0, 'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6];
        $assignedDayNums = $this->getAssignedDayNumbers($doctor->visiting_days, $dayMap);

        while ($current->lte($attendanceEndDate)) {
            if (in_array($current->dayOfWeek, $assignedDayNums)) {
                $daysAssignedCount++;

                $dateStr = $current->format('Y-m-d');
                $dailyApts = $appointmentsByDate->get($dateStr, collect());

                if ($dailyApts->isNotEmpty()) {
                    $allCancelled = $dailyApts->every(function ($apt) {
                        $status = strtolower($apt->status);
                        return $status === 'cancelled' || $status === 'no-show';
                    });
                    if ($allCancelled)
                        $daysAbsentCount++;
                }
            }
            $current->addDay();
        }

        // Calculate total unique patients (excluding cancelled/no-show)
        $totalPatients = $appointments->filter(function ($apt) {
            $status = strtolower($apt->status);
            return $status !== 'cancelled' && $status !== 'no-show';
        })->pluck('patient_id')->unique()->count();

        // Calculate average patients per day
        $daysWithWork = $appointmentsByDate->filter(function ($dayApts) {
            return $dayApts->contains(function ($apt) {
                $status = strtolower($apt->status);
                return $status !== 'cancelled' && $status !== 'no-show';
            });
        })->count();

        $averagePatientsPerDay = $daysWithWork > 0 ? round($totalPatients / $daysWithWork, 2) : 0;
        $completionRate = $totalAppointmentsScheduled > 0 ? round(($completedAppointments / $totalAppointmentsScheduled) * 100, 2) : 0;

        return [
            'days_assigned' => $daysAssignedCount,
            'days_absent' => $daysAbsentCount,
            'total_patients' => $totalPatients,
            'total_revenue' => $totalRevenue,
            'completed_appointments' => $completedAppointments,
            'scheduled_appointments' => $totalAppointmentsScheduled,
            'cancelled_appointments' => $cancelledAppointments,
            'no_show_appointments' => $noShowAppointments,
            'average_patients_per_day' => $averagePatientsPerDay,
            'completion_rate' => $completionRate,
        ];
    }

    /**
     * Helper to get assigned day numbers from visiting days string
     */
    private function getAssignedDayNumbers($visitingDaysStr, $dayMap)
    {
        if (empty($visitingDaysStr))
            return [];
        $assignedDayNumbers = [];
        $visitingDays = json_decode($visitingDaysStr, true);

        if (!is_array($visitingDays)) {
            $rawStr = trim($visitingDaysStr);
            if (preg_match('/^(\w+)\s+to\s+(\w+)$/i', $rawStr, $matches)) {
                $startNum = $dayMap[ucfirst(strtolower($matches[1]))] ?? null;
                $endNum = $dayMap[ucfirst(strtolower($matches[2]))] ?? null;
                if ($startNum !== null && $endNum !== null) {
                    if ($startNum <= $endNum) {
                        for ($i = $startNum; $i <= $endNum; $i++)
                            $assignedDayNumbers[] = $i;
                    } else {
                        // Fri(5) to Mon(1) -> 5,6,0,1
                        for ($i = $startNum; $i <= 6; $i++)
                            $assignedDayNumbers[] = $i;
                        for ($i = 0; $i <= $endNum; $i++)
                            $assignedDayNumbers[] = $i;
                    }
                }
            } else {
                foreach (explode(',', $rawStr) as $day) {
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
        return array_unique($assignedDayNumbers);
    }

    /**
     * Calculate number of days assigned based on visiting days string
     */
    private function calculateDaysAssigned($visitingDaysStr, $startDate, $endDate)
    {
        $dayMap = [
            'Sunday' => 0,
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
        ];
        $assignedDayNumbers = $this->getAssignedDayNumbers($visitingDaysStr, $dayMap);

        $daysCount = 0;
        $current = $startDate->copy();
        while ($current <= $endDate) {
            if (in_array($current->dayOfWeek, $assignedDayNumbers)) {
                $daysCount++;
            }
            $current->addDay();
        }
        return $daysCount;
    }
}
