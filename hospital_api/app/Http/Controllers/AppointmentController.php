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
        return response()->json($appointments, 200);
    }

    // GET appointments for a specific patient
    public function getPatientAppointments($patientId)
    {
        $appointments = Appointment::where('patient_id', $patientId)->get();
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

            // 2. Enforce 50 patient limit
            if ($existingCount >= 50) {
                return response()->json([
                    'message' => 'This doctor has reached the maximum limit of 50 patients for this day.'
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
            $appointment->status = $request->input('status', 'scheduled');

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
}
