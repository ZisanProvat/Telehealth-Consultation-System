<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
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

        // Try to get cached report first
        $report = DoctorMonthlyReport::where('doctor_id', $doctorId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        // If not found, generate it
        if (!$report) {
            $reportData = $this->calculateMonthlyReport($doctorId, $month, $year);
            $report = DoctorMonthlyReport::create(array_merge($reportData, [
                'doctor_id' => $doctorId,
                'month' => $month,
                'year' => $year,
            ]));
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
        $month = $request->query('month', date('n'));
        $year = $request->query('year', date('Y'));

        $doctors = Doctor::all();
        $reports = [];

        foreach ($doctors as $doctor) {
            // Try to get cached report
            $report = DoctorMonthlyReport::where('doctor_id', $doctor->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            // If not found, generate it
            if (!$report) {
                $reportData = $this->calculateMonthlyReport($doctor->id, $month, $year);
                $report = DoctorMonthlyReport::create(array_merge($reportData, [
                    'doctor_id' => $doctor->id,
                    'month' => $month,
                    'year' => $year,
                ]));
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

        return response()->json([
            'success' => true,
            'data' => $reports
        ]);
    }

    /**
     * Get yearly report for a specific doctor (all 12 months)
     */
    public function getYearlyReport($doctorId, Request $request)
    {
        $year = $request->query('year', date('Y'));
        $reports = [];

        for ($month = 1; $month <= 12; $month++) {
            $report = DoctorMonthlyReport::where('doctor_id', $doctorId)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if (!$report) {
                $reportData = $this->calculateMonthlyReport($doctorId, $month, $year);
                $report = DoctorMonthlyReport::create(array_merge($reportData, [
                    'doctor_id' => $doctorId,
                    'month' => $month,
                    'year' => $year,
                ]));
            }

            $reports[] = $report;
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
    private function calculateMonthlyReport($doctorId, $month, $year)
    {
        $doctor = Doctor::findOrFail($doctorId);

        // Get start and end dates for the month
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Get all appointments for this doctor in this month
        $appointments = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('appointment_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        // Count appointments by status
        $completedAppointments = $appointments->where('status', 'completed')->count();
        $scheduledAppointments = $appointments->where('status', 'scheduled')->count();
        $cancelledAppointments = $appointments->where('status', 'cancelled')->count();
        $noShowAppointments = $appointments->where('status', 'no-show')->count();

        // Total appointments scheduled this month (all statuses)
        $totalAppointmentsScheduled = $appointments->count();

        // Calculate total revenue from PAID appointments (regardless of completion status)
        // This counts:
        // 1. Any appointment with payment_status = 'paid' (even if scheduled/pending)
        // 2. Completed appointments with null payment_status (legacy data)
        $totalRevenue = $appointments->filter(function ($apt) {
            // Count if payment is confirmed as paid (any status)
            if ($apt->payment_status === 'paid' && $apt->amount > 0) {
                return true;
            }
            // Also count completed appointments with null payment_status (legacy)
            if ($apt->status === 'completed' && $apt->payment_status === null && $apt->amount > 0) {
                return true;
            }
            return false;
        })->sum('amount');

        // Calculate days assigned based on visiting_days
        $visitingDays = json_decode($doctor->visiting_days, true) ?? [];
        $daysAssigned = $this->calculateDaysAssigned($visitingDays, $startDate, $endDate);

        // Calculate days absent (days assigned but no appointments)
        $daysWithAppointments = $appointments->pluck('appointment_date')
            ->map(function ($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })
            ->unique()
            ->count();

        $daysAbsent = max(0, $daysAssigned - $daysWithAppointments);

        // Calculate average patients per day
        $averagePatientsPerDay = $daysWithAppointments > 0
            ? round($completedAppointments / $daysWithAppointments, 2)
            : 0;

        // Calculate completion rate
        $totalScheduled = $completedAppointments + $scheduledAppointments + $cancelledAppointments + $noShowAppointments;
        $completionRate = $totalScheduled > 0
            ? round(($completedAppointments / $totalScheduled) * 100, 2)
            : 0;

        return [
            'days_assigned' => $daysAssigned,
            'days_absent' => $daysAbsent,
            'total_patients' => $completedAppointments,
            'total_revenue' => $totalRevenue,
            'completed_appointments' => $completedAppointments,
            'scheduled_appointments' => $totalAppointmentsScheduled, // Changed to total appointments
            'cancelled_appointments' => $cancelledAppointments,
            'no_show_appointments' => $noShowAppointments,
            'average_patients_per_day' => $averagePatientsPerDay,
            'completion_rate' => $completionRate,
        ];
    }

    /**
     * Calculate number of days assigned based on visiting days
     */
    private function calculateDaysAssigned($visitingDays, $startDate, $endDate)
    {
        if (empty($visitingDays)) {
            return 0;
        }

        $daysCount = 0;
        $current = $startDate->copy();

        // Map day names to Carbon day numbers (0 = Sunday, 6 = Saturday)
        $dayMap = [
            'Sunday' => 0,
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
        ];

        // Convert visiting days to day numbers
        $assignedDayNumbers = [];
        foreach ($visitingDays as $day) {
            if (isset($dayMap[$day])) {
                $assignedDayNumbers[] = $dayMap[$day];
            }
        }

        // Count days in the month that match assigned days
        while ($current <= $endDate) {
            if (in_array($current->dayOfWeek, $assignedDayNumbers)) {
                $daysCount++;
            }
            $current->addDay();
        }

        return $daysCount;
    }
}
