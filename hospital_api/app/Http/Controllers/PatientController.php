<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\PatientVerificationMail;

class PatientController extends Controller
{
    public function register(Request $req)
    {
        $req->validate([
            'name' => 'required|string|regex:/^[a-zA-Z\s\.]+$/u',
            'phone' => 'required|digits:11',
            'email' => 'required|email',
            'address' => 'required|string',
            'age' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'weight' => 'required|numeric|min:0',
            'blood_group' => 'required|string',
            'gender' => 'required|string',
            'password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/', // At least one uppercase
                'regex:/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/' // At least one special char
            ],
        ], [
            'name.regex' => 'The name may only contain letters, spaces and dots.',
            'phone.digits' => 'The phone number must be exactly 11 digits.',
            'password.regex' => 'The password must contain at least one uppercase letter and one special character.',
            'password.min' => 'The password must be at least 8 characters.',
            'age.min' => 'Age cannot be negative.',
            'height.min' => 'Height cannot be negative.',
            'weight.min' => 'Weight cannot be negative.',
        ]);

        // Check if email exists but is not verified
        $existingPatient = Patient::where('email', $req->input('email'))->first();

        if ($existingPatient) {
            if ($existingPatient->email_verified_at) {
                return response()->json(['message' => 'The email has already been taken.'], 422);
            }
            // Overwrite existing unverified patient
            $patient = $existingPatient;
        } else {
            $patient = new Patient;
            $patient->email = $req->input('email');
        }

        $patient->name = $req->input('name');
        $patient->phone = $req->input('phone');
        $patient->address = $req->input('address');
        $patient->age = $req->input('age');
        $patient->height = $req->input('height');
        $patient->weight = $req->input('weight');
        $patient->blood_group = $req->input('blood_group');
        $patient->gender = $req->input('gender');
        $patient->previous_record = $req->input('previous_record', 'no');
        $patient->uploaded_record = $req->input('uploaded_record');
        $patient->password = Hash::make($req->input('password'));

        // Email Verification Logic
        $patient->verification_token = Str::random(64);
        $patient->email_verified_at = null;

        $patient->save();

        try {
            Mail::to($patient->email)->send(new PatientVerificationMail($patient, $patient->verification_token));
        } catch (\Exception $e) {
            \Log::error("Mail sending failed: " . $e->getMessage());
            // Continue registration but maybe warn? For now assume it works or log it.
        }

        return response()->json([
            'message' => 'Registration successful! Please check your email to verify your account.',
            'user' => $patient
        ], 201);
    }
    public function login(Request $req)
    {
        $patient = Patient::where('email', $req->input('email'))->first();
        if (!$patient || !Hash::check($req->input('password'), $patient->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // if ($patient->email_verified_at === null) {
        //     return response()->json(['message' => 'Please verify your email address before logging in.'], 403);
        // }

        // Add role to patient object
        $patient->role = 'patient';

        return response()->json([
            'user' => $patient,
            'token' => 'patient_token_' . $patient->id,
            'message' => 'Login successful'
        ], 200);
    }

    public function show($id)
    {
        try {
            $patient = Patient::findOrFail($id);
            return response()->json($patient, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Patient not found'], 404);
        }
    }

    public function uploadHealthRecord(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
        ]);

        try {
            $patient = Patient::findOrFail($id);

            if ($request->hasFile('file')) {
                // Get existing records
                $existingRecords = [];
                if ($patient->uploaded_record) {
                    $decoded = json_decode($patient->uploaded_record, true);
                    if (is_array($decoded)) {
                        $existingRecords = $decoded;
                    } else {
                        // Handle legacy single file path
                        $existingRecords[] = $patient->uploaded_record;
                    }
                }

                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $path = $file->storeAs('health_records/' . $id, $originalName, 'public');
                $existingRecords[] = $path; // Append new file path

                $patient->uploaded_record = json_encode($existingRecords);
                $patient->previous_record = 'yes';
                $patient->save();

                return response()->json([
                    'message' => 'Health record uploaded successfully',
                    'data' => $patient
                ], 200);
            }

            return response()->json(['message' => 'No file provided'], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }
    public function updateProfile(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'sometimes|required|regex:/^[a-zA-Z\s\.]+$/u',
                'age' => 'nullable|numeric|min:0',
                'height' => 'nullable|numeric|min:0',
                'weight' => 'nullable|numeric|min:0',
            ], [
                'name.regex' => 'The name may only contain letters, spaces and dots.',
                'age.min' => 'Age cannot be negative.',
                'height.min' => 'Height cannot be negative.',
                'weight.min' => 'Weight cannot be negative.',
            ]);

            $patient = Patient::findOrFail($id);

            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($patient->photo && \Storage::disk('public')->exists($patient->photo)) {
                    \Storage::disk('public')->delete($patient->photo);
                }
                $path = $request->file('photo')->store('profile_photos', 'public');
                $patient->photo = $path;
            }

            $fields = ['name', 'phone', 'address', 'email', 'age', 'height', 'weight', 'blood_group', 'gender'];
            foreach ($fields as $field) {
                if ($request->has($field)) {
                    $patient->$field = $request->input($field);
                }
            }

            $patient->save();

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $patient
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    public function deleteHealthRecord(Request $request, $id)
    {
        $request->validate([
            'file_path' => 'required|string',
        ]);

        try {
            $patient = Patient::findOrFail($id);
            $filePath = $request->input('file_path');

            if ($patient->uploaded_record) {
                $records = json_decode($patient->uploaded_record, true);
                if (!is_array($records)) {
                    $records = [$patient->uploaded_record];
                }

                // Remove the path from array
                $key = array_search($filePath, $records);
                if ($key !== false) {
                    unset($records[$key]);

                    // Delete the physical file
                    if (\Storage::disk('public')->exists($filePath)) {
                        \Storage::disk('public')->delete($filePath);
                    }

                    $patient->uploaded_record = json_encode(array_values($records));

                    if (empty($records)) {
                        $patient->previous_record = 'no';
                        $patient->uploaded_record = null;
                    }

                    $patient->save();

                    return response()->json([
                        'message' => 'Record deleted successfully',
                        'data' => $patient
                    ], 200);
                }
            }

            return response()->json(['message' => 'File not found in records'], 404);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Deletion failed: ' . $e->getMessage()], 500);
        }
    }

    public function changePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6',
        ]);

        try {
            $patient = Patient::findOrFail($id);

            if (!Hash::check($request->input('current_password'), $patient->password)) {
                return response()->json(['message' => 'Incorrect current password'], 400);
            }

            $patient->password = Hash::make($request->input('new_password'));
            $patient->save();

            return response()->json(['message' => 'Password changed successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Password change failed: ' . $e->getMessage()], 500);
        }
    }

    public function verifyPatientEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        \Log::info("Verifying email with token: " . $request->token);
        $patient = Patient::where('verification_token', $request->token)->first();

        if (!$patient) {
            \Log::error("Invalid token: " . $request->token);
            return response()->json(['message' => 'Invalid or expired verification token.'], 400);
        }

        if ($patient->email_verified_at) {
            return response()->json(['message' => 'Email already verified.'], 200);
        }

        $patient->email_verified_at = now();
        // $patient->verification_token = null; // Keep token to allow re-verification checks
        $patient->save();

        return response()->json(['message' => 'Email verified successfully!'], 200);
    }
}