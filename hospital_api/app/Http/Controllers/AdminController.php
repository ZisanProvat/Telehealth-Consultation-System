<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $admin->createToken('admin_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $admin,
            'token' => $token,
            'access_token' => $token,
        ], 200);
    }

    public function getDashboardStats()
    {
        try {
            $totalPatients = Patient::count();
            $totalDoctors = Doctor::count();
            $totalAppointments = Appointment::count();

            $recentAppointments = Appointment::orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($appointment) {
                    $doctor = Doctor::find($appointment->doctor_id);
                    $patient = Patient::find($appointment->patient_id);
                    return [
                        'id' => $appointment->id,
                        'doctor_id' => $appointment->doctor_id,
                        'patient_id' => $appointment->patient_id,
                        'doctor_name' => $doctor ? $doctor->full_name : 'Unknown',
                        'patient_name' => $patient ? $patient->name : 'Unknown',
                        'date' => $appointment->appointment_date,
                        'status' => $appointment->status,
                    ];
                });

            return response()->json([
                'stats' => [
                    'total_patients' => $totalPatients,
                    'total_doctors' => $totalDoctors,
                    'total_appointments' => $totalAppointments,
                ],
                'recent_appointments' => $recentAppointments
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch stats: ' . $e->getMessage()], 500);
        }
    }
    // --- Patient Management ---
    public function getPatients()
    {
        return response()->json(Patient::all(), 200);
    }

    public function deletePatient($id)
    {
        $patient = Patient::find($id);
        if ($patient) {
            $patient->delete();
            return response()->json(['message' => 'Patient deleted successfully'], 200);
        }
        return response()->json(['message' => 'Patient not found'], 404);
    }

    public function storePatient(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|regex:/^[a-zA-Z\s\.]+$/u',
                'email' => 'required|email|unique:patients',
                'password' => 'required|min:6',
                'age' => 'nullable|numeric|min:0',
                'weight' => 'nullable|numeric|min:0',
                'height' => 'nullable|numeric|min:0',
            ], [
                'name.regex' => 'The name may only contain letters, spaces and dots.',
                'age.min' => 'Age cannot be negative.',
                'weight.min' => 'Weight cannot be negative.',
                'height.min' => 'Height cannot be negative.'
            ]);

            $patient = new Patient();
            $patient->name = $request->name;
            $patient->email = $request->email;
            $patient->password = Hash::make($request->password);
            $patient->phone = $request->phone;
            $patient->address = $request->address;
            $patient->age = $request->age ?: null;
            $patient->height = $request->height ?: null;
            $patient->weight = $request->weight ?: null;
            $patient->blood_group = $request->blood_group;
            $patient->gender = $request->gender;

            // Set default values for other non-nullable fields
            $patient->previous_record = 'no';
            $patient->uploaded_record = null;

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('patients', 'public');
                $patient->photo = $path;
            }

            $patient->save();

            return response()->json(['message' => 'Patient added successfully', 'patient' => $patient], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to add patient: ' . $e->getMessage()], 500);
        }
    }

    public function updatePatient(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'sometimes|required|regex:/^[a-zA-Z\s\.]+$/u',
                'email' => 'sometimes|required|email|unique:patients,email,' . $id,
                'age' => 'nullable|numeric|min:0',
                'weight' => 'nullable|numeric|min:0',
                'height' => 'nullable|numeric|min:0',
            ], [
                'name.regex' => 'The name may only contain letters, spaces and dots.',
                'age.min' => 'Age cannot be negative.',
                'weight.min' => 'Weight cannot be negative.',
                'height.min' => 'Height cannot be negative.'
            ]);

            $patient = Patient::find($id);

            $patient->name = $request->name ?? $patient->name;
            $patient->email = $request->email ?? $patient->email;
            if ($request->password) {
                $patient->password = Hash::make($request->password);
            }
            $patient->phone = $request->phone ?? $patient->phone;
            $patient->address = $request->address ?? $patient->address;
            $patient->age = $request->age ?: $patient->age;
            $patient->height = $request->height ?: $patient->height;
            $patient->weight = $request->weight ?: $patient->weight;
            $patient->blood_group = $request->blood_group ?? $patient->blood_group;
            $patient->gender = $request->gender ?? $patient->gender;

            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($patient->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($patient->photo)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($patient->photo);
                }
                $path = $request->file('photo')->store('patients', 'public');
                $patient->photo = $path;
            }

            $patient->save();

            return response()->json(['message' => 'Patient updated successfully', 'patient' => $patient], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update patient: ' . $e->getMessage()], 500);
        }
    }

    // --- Doctor Management ---
    public function getDoctors()
    {
        return response()->json(Doctor::all(), 200);
    }

    public function storeDoctor(Request $request)
    {
        try {
            $request->validate([
                'full_name' => 'required|regex:/^[a-zA-Z\s\.]+$/u',
                'email' => 'required|email|unique:doctors',
                'password' => 'required|min:6',
                'fees' => 'nullable|numeric|min:0',
                'experience' => 'nullable|numeric|min:0',
            ], [
                'full_name.regex' => 'The name may only contain letters, spaces and dots.',
                'fees.min' => 'Consultation fees cannot be negative.',
                'experience.min' => 'Experience cannot be negative.'
            ]);

            $doctor = new Doctor();
            $doctor->full_name = $request->full_name;
            $doctor->email = $request->email;
            $doctor->password = Hash::make($request->password);
            $doctor->phone = $request->phone;
            $doctor->specialization = $request->specialization;
            $doctor->qualification = $request->qualification;
            $doctor->experience = $request->experience;
            $doctor->designation = $request->designation;
            $doctor->bmdc_no = $request->bmdc_no;
            $doctor->visiting_days = $request->visiting_days;
            $doctor->visiting_hours = $request->visiting_hours;
            $doctor->fees = $request->fees;
            $doctor->affiliated_clinic = $request->affiliated_clinic;
            $doctor->description = $request->description;

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('doctors', 'public');
                $doctor->photo = $path;
            }

            $doctor->save();

            return response()->json(['message' => 'Doctor added successfully', 'doctor' => $doctor], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to add doctor: ' . $e->getMessage()], 500);
        }
    }

    public function updateDoctor(Request $request, $id)
    {
        try {
            $request->validate([
                'full_name' => 'sometimes|required|regex:/^[a-zA-Z\s\.]+$/u',
                'fees' => 'nullable|numeric|min:0',
                'experience' => 'nullable|numeric|min:0',
            ], [
                'full_name.regex' => 'The name may only contain letters, spaces and dots.',
                'fees.min' => 'Consultation fees cannot be negative.',
                'experience.min' => 'Experience cannot be negative.'
            ]);

            $doctor = Doctor::find($id);
            if (!$doctor) {
                return response()->json(['message' => 'Doctor not found'], 404);
            }

            $doctor->full_name = $request->full_name ?? $doctor->full_name;
            $doctor->email = $request->email ?? $doctor->email;
            if ($request->password) {
                $doctor->password = Hash::make($request->password);
            }
            $doctor->phone = $request->phone ?? $doctor->phone;
            $doctor->specialization = $request->specialization ?? $doctor->specialization;
            $doctor->qualification = $request->qualification ?? $doctor->qualification;
            $doctor->experience = $request->experience ?? $doctor->experience;
            $doctor->designation = $request->designation ?? $doctor->designation;
            $doctor->bmdc_no = $request->bmdc_no ?? $doctor->bmdc_no;
            $doctor->visiting_days = $request->visiting_days ?? $doctor->visiting_days;
            $doctor->visiting_hours = $request->visiting_hours ?? $doctor->visiting_hours;
            $doctor->fees = $request->fees ?? $doctor->fees;
            $doctor->affiliated_clinic = $request->affiliated_clinic ?? $doctor->affiliated_clinic;
            $doctor->description = $request->description ?? $doctor->description;

            if ($request->hasFile('photo')) {
                // Delete old photo
                if ($doctor->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($doctor->photo)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($doctor->photo);
                }
                $path = $request->file('photo')->store('doctors', 'public');
                $doctor->photo = $path;
            }

            $doctor->save();

            return response()->json(['message' => 'Doctor updated successfully', 'doctor' => $doctor], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update doctor: ' . $e->getMessage()], 500);
        }
    }

    public function deleteDoctor($id)
    {
        $doctor = Doctor::find($id);
        if ($doctor) {
            $doctor->delete();
            return response()->json(['message' => 'Doctor deleted successfully'], 200);
        }
        return response()->json(['message' => 'Doctor not found'], 404);
    }

    // --- Appointment Management ---
    public function getAllAppointments()
    {
        $appointments = Appointment::orderBy('created_at', 'desc')->get()
            ->map(function ($appointment) {
                $doctor = Doctor::find($appointment->doctor_id);
                $patient = Patient::find($appointment->patient_id);
                return [
                    'id' => $appointment->id,
                    'doctor_id' => $appointment->getAttribute('doctor_id'),
                    'patient_id' => $appointment->getAttribute('patient_id'),
                    'doctor_name' => $doctor ? $doctor->full_name : 'Unknown',
                    'patient_name' => $patient ? $patient->name : 'Unknown',
                    'date' => $appointment->appointment_date,
                    'day' => $appointment->day,
                    'status' => $appointment->status,
                    'serial_number' => $appointment->serial_number,
                    'reason' => $appointment->reason,
                ];
            });
        return response()->json($appointments, 200);
    }

    public function updateAppointmentStatus(Request $request, $id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        $appointment->status = $request->status;
        $appointment->save();

        return response()->json(['message' => 'Appointment status updated', 'appointment' => $appointment], 200);
    }

    public function deleteAppointment($id)
    {
        $appointment = Appointment::find($id);
        if ($appointment) {
            $appointment->delete();
            return response()->json(['message' => 'Appointment deleted successfully'], 200);
        }
        return response()->json(['message' => 'Appointment not found'], 404);
    }

    // --- Payment Management ---
    public function getPaymentHistory()
    {
        $payments = Appointment::whereNotNull('payment_status')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($appointment) {
                $doctor = Doctor::find($appointment->doctor_id);
                $patient = Patient::find($appointment->patient_id);
                return [
                    'id' => $appointment->id,
                    'transaction_id' => $appointment->transaction_id,
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'patient_name' => $patient ? $patient->name : 'Unknown',
                    'doctor_name' => $doctor ? $doctor->full_name : 'Unknown',
                    'amount' => $appointment->amount,
                    'payment_method' => $appointment->payment_method,
                    'payment_status' => $appointment->payment_status,
                    'appointment_date' => $appointment->appointment_date,
                    'payment_date' => $appointment->created_at,
                ];
            });
        return response()->json($payments, 200);
    }

    // --- Profile Management ---
    public function updateProfile(Request $request)
    {
        // Get admin ID from request or use first admin as fallback
        $adminId = $request->input('admin_id');
        $admin = $adminId ? Admin::find($adminId) : Admin::first();

        if (!$admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        try {
            $request->validate([
                'name' => 'required|string|regex:/^[a-zA-Z\s\.]+$/u',
                'email' => 'required|email|unique:admins,email,' . $admin->id,
            ], [
                'name.regex' => 'The name may only contain letters, spaces and dots.'
            ]);

            $admin->name = $request->name;
            $admin->email = $request->email;

            if ($request->hasFile('photo')) {
                // Delete old photo
                if ($admin->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($admin->photo)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($admin->photo);
                }
                $path = $request->file('photo')->store('admin_photos', 'public');
                $admin->photo = $path;
            }

            $admin->save();

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $admin
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        // Get admin ID from request or use first admin as fallback
        $adminId = $request->input('admin_id');
        $admin = $adminId ? Admin::find($adminId) : Admin::first();

        if (!$admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        if (!Hash::check($request->current_password, $admin->password)) {
            return response()->json(['message' => 'Incorrect current password'], 400);
        }

        $admin->password = Hash::make($request->new_password);
        $admin->save();

        return response()->json(['message' => 'Password changed successfully'], 200);
    }
}
