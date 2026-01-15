<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    /**
     * Store a new prescription
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|integer',
            'doctor_id' => 'required|integer',
            'patient_id' => 'required|integer',
            'doctor_name' => 'required|string',
            'patient_name' => 'required|string',
            'appointment_date' => 'required|string',
            'serial_number' => 'nullable|string',
            'appointment_time' => 'nullable|string',
            'prescription_content' => 'required|string',
        ]);

        $prescription = Prescription::create($validated);

        // Update appointment status to completed when prescription is sent (optional but logical)
        $appointment = Appointment::find($request->appointment_id);
        if ($appointment && $appointment->status !== 'completed') {
            $appointment->update(['status' => 'completed']);
        }

        $prescription->load('doctor');

        return response()->json([
            'success' => true,
            'message' => 'Prescription sent successfully',
            'data' => $prescription
        ]);
    }

    /**
     * Get prescriptions for a specific patient
     */
    public function getPatientPrescriptions($patientId)
    {
        $prescriptions = Prescription::where('patient_id', $patientId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($prescriptions);
    }

    /**
     * Get prescription for a specific appointment
     */
    public function getByAppointment($appointmentId)
    {
        $prescription = Prescription::with('doctor')->where('appointment_id', $appointmentId)->first();

        if (!$prescription) {
            return response()->json([
                'success' => false,
                'message' => 'Prescription not found'
            ], 404);
        }

        return response()->json($prescription);
    }

    /**
     * Get prescriptions written by a specific doctor
     */
    public function getDoctorPrescriptions($doctorId)
    {
        $prescriptions = Prescription::where('doctor_id', $doctorId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($prescriptions);
    }
}
