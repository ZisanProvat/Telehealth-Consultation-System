<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    // GET all appointments
    public function index()
    {
        $appointments = Appointment::all();

        // Add calculated time slots to each appointment
        $appointments = $appointments->map(function ($appointment) {
            $doctor = \App\Models\Doctor::find($appointment->doctor_id);
            $visitingHours = $doctor ? $doctor->visiting_hours : null;

            $timeSlot = $this->calculateTimeSlot($appointment->serial_number, $visitingHours);
            $appointment->appointment_time_slot = $timeSlot;

            return $appointment;
        });

        return response()->json($appointments, 200);
    }

    // GET appointments for a specific patient
    public function getPatientAppointments($patientId)
    {
        $appointments = Appointment::where('patient_id', $patientId)->get();

        // Add calculated time slots to each appointment
        $appointments = $appointments->map(function ($appointment) {
            $doctor = \App\Models\Doctor::find($appointment->doctor_id);
            $visitingHours = $doctor ? $doctor->visiting_hours : null;

            $timeSlot = $this->calculateTimeSlot($appointment->serial_number, $visitingHours);
            $appointment->appointment_time_slot = $timeSlot;

            return $appointment;
        });

        return response()->json($appointments, 200);
    }

    // GET appointments for a specific doctor
    public function getDoctorAppointments($doctorId)
    {
        $appointments = Appointment::with('patient:id,uploaded_record')
            ->where('doctor_id', $doctorId)
            ->get();

        // Add calculated time slots to each appointment
        $doctor = \App\Models\Doctor::find($doctorId);
        $visitingHours = $doctor ? $doctor->visiting_hours : null;

        $appointments = $appointments->map(function ($appointment) use ($visitingHours) {
            $timeSlot = $this->calculateTimeSlot($appointment->serial_number, $visitingHours);
            $appointment->appointment_time_slot = $timeSlot;

            return $appointment;
        });

        return response()->json($appointments, 200);
    }

    // POST create appointment
    public function store(Request $request)
    {
        try {
            $doctorId = $request->input('doctor_id');
            $date = $request->input('appointment_date');

            // 1. Check existing appointments for this doctor on this date
            $existingCount = Appointment::where('doctor_id', $doctorId)
                ->where('appointment_date', $date)
                ->count();

            // 2. Enforce Dynamic Patient Limit based on Visiting Hours
            $doctor = \App\Models\Doctor::find($doctorId);
            $maxPatients = 50; // Default fallback

            if ($doctor && $doctor->visiting_hours) {
                // Flexible parsing for formats: "09:00 AM - 05:00 PM", "9 AM to 5 PM", "9:00-17:00"
                $rawHours = strtolower($doctor->visiting_hours);
                // Split by 'to' or '-'
                $parts = preg_split('/\s*(?:to|-)\s*/', $rawHours);

                if (count($parts) === 2) {
                    $parseTime = function ($timeStr) {
                        if (preg_match('/(\d{1,2})(?::(\d{2}))?\s*(am|pm)?/', $timeStr, $m)) {
                            $h = (int) $m[1];
                            $min = isset($m[2]) ? (int) $m[2] : 0;
                            $meridiem = isset($m[3]) ? $m[3] : null;

                            if ($meridiem === 'pm' && $h !== 12)
                                $h += 12;
                            if ($meridiem === 'am' && $h === 12)
                                $h = 0;

                            return $h * 60 + $min;
                        }
                        return null;
                    };

                    $startMin = $parseTime($parts[0]);
                    $endMin = $parseTime($parts[1]);

                    if ($startMin !== null && $endMin !== null) {
                        // Handle overnight (e.g. 10 PM to 2 AM)
                        if ($endMin < $startMin) {
                            $endMin += 24 * 60;
                        }

                        $durationMinutes = $endMin - $startMin;
                        if ($durationMinutes > 0) {
                            $maxPatients = floor($durationMinutes / 20);
                        }
                    }
                }
            }

            if ($existingCount >= $maxPatients) {
                return response()->json([
                    'message' => "This doctor has reached the maximum limit of {$maxPatients} patients for this day ({$doctor->visiting_hours})."
                ], 422);
            }

            $appointment = new Appointment();

            // Doctor information
            $appointment->doctor_id = $doctorId;
            $appointment->doctor_name = $request->input('doctor_name');

            // Patient information
            $appointment->patient_id = $request->input('patient_id');
            $appointment->patient_name = $request->input('patient_name');

            // Appointment details
            $appointment->appointment_date = $date;
            $appointment->serial_number = $existingCount + 1; // Assign automatic serial
            $appointment->day = $request->input('day'); // Day of week
            $appointment->reason = $request->input('reason');
            $appointment->notes = $request->input('notes');
            $appointment->status = $request->input('status', 'pending_payment');

            // Payment information
            $appointment->payment_method = $request->input('payment_method');
            $appointment->payment_status = $request->input('payment_status', 'pending');

            // Automatically set amount from doctor's fees if not provided
            if ($request->has('amount')) {
                $appointment->amount = $request->input('amount');
            } else {
                // Fetch doctor's fee and set as amount
                $doctor = \App\Models\Doctor::find($doctorId);
                if ($doctor && $doctor->fees) {
                    $appointment->amount = $doctor->fees;
                }
            }

            // Generate Video Call Link (using Jitsi for guaranteed shared room without API setup)
            // Pattern: TeleHealth-Appt-{ID}-{RandomString}
            // Jitsi allows deterministic, instantly usable rooms.
            // We'll store this in the 'notes' field as requested to avoid DB changes.

            // Generate link
            $uniqueRoomId = 'TeleHealth-' . uniqid() . '-' . $appointment->serial_number;
            $videoLink = "https://meet.jit.si/" . $uniqueRoomId;

            // Combine with user notes (Prepending ensures it's easily visible/parsable)
            $userNotes = $request->input('notes');
            $appointment->notes = $videoLink . "\n\n" . ($userNotes ? "Patient Notes: " . $userNotes : "");

            $appointment->save();

            return response()->json([
                'message' => 'Appointment booked successfully. Your serial number is ' . $appointment->serial_number,
                'data' => $appointment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to book appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // PUT update appointment
    public function update(Request $request, $id)
    {
        try {
            $appointment = Appointment::findOrFail($id);

            if ($request->has('doctor_id')) {
                $appointment->doctor_id = $request->input('doctor_id');
            }
            if ($request->has('doctor_name')) {
                $appointment->doctor_name = $request->input('doctor_name');
            }
            if ($request->has('patient_id')) {
                $appointment->patient_id = $request->input('patient_id');
            }
            if ($request->has('patient_name')) {
                $appointment->patient_name = $request->input('patient_name');
            }
            if ($request->has('appointment_date')) {
                $appointment->appointment_date = $request->input('appointment_date');
            }
            if ($request->has('appointment_time')) {
                $appointment->appointment_time = $request->input('appointment_time');
            }
            if ($request->has('day')) {
                $appointment->day = $request->input('day');
            }
            if ($request->has('reason')) {
                $appointment->reason = $request->input('reason');
            }
            if ($request->has('notes')) {
                $appointment->notes = $request->input('notes');
            }
            if ($request->has('status')) {
                $appointment->status = $request->input('status');
            }

            $appointment->save();

            return response()->json([
                'message' => 'Appointment updated successfully',
                'data' => $appointment
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // DELETE appointment
    public function destroy($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->delete();

            return response()->json([
                'message' => 'Appointment deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate appointment time slot based on serial number
     * Each patient gets 20 minutes
     * 
     * @param int $serialNumber - The patient's serial number (1, 2, 3, etc.)
     * @param string $visitingHours - Doctor's visiting hours (e.g., "09:00 AM - 05:00 PM")
     * @return array - ['start_time' => '09:00 AM', 'end_time' => '09:20 AM']
     */
    private function calculateTimeSlot($serialNumber, $visitingHours)
    {
        // Default to 9 AM if visiting hours not set
        $startTime = '09:00';

        // Parse visiting hours if available
        if ($visitingHours) {
            // Flexible parsing for formats: "09:00 AM - 05:00 PM", "9 AM to 5 PM", "9:00-17:00"
            $rawHours = strtolower($visitingHours);
            $parts = preg_split('/\s*(?:to|-)\s*/', $rawHours);

            if (!empty($parts[0])) {
                // Parse the start time part
                if (preg_match('/(\d{1,2})(?::(\d{2}))?\s*(am|pm)?/', $parts[0], $matches)) {
                    $h = (int) $matches[1];
                    $min = isset($matches[2]) ? (int) $matches[2] : 0;
                    $meridiem = isset($matches[3]) ? $matches[3] : null;

                    if ($meridiem === 'pm' && $h !== 12)
                        $h += 12;
                    if ($meridiem === 'am' && $h === 12)
                        $h = 0;

                    // Create DateTime from calculated 24h hour/minute
                    $time = new \DateTime();
                    $time->setTime($h, $min);
                    $startTime = $time->format('H:i');
                }
            }
        }

        // Calculate time slot: (serial - 1) * 20 minutes from start time
        $minutesFromStart = ($serialNumber - 1) * 20;

        $slotStart = new \DateTime($startTime);
        $slotStart->modify("+{$minutesFromStart} minutes");

        $slotEnd = clone $slotStart;
        $slotEnd->modify('+20 minutes');

        return [
            'start_time' => $slotStart->format('h:i A'),
            'end_time' => $slotEnd->format('h:i A'),
            'start_time_24h' => $slotStart->format('H:i'),
            'end_time_24h' => $slotEnd->format('H:i'),
        ];
    }
}
