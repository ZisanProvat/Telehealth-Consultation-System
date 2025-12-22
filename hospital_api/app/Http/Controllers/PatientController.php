<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use Illuminate\Support\Facades\Hash;

class PatientController extends Controller
{
    public function register(Request $req)
    {
        $req->validate([
            'name' => 'required|string|regex:/^[A-Z]/',
            'phone' => 'required|digits:11',
            'email' => 'required|email|unique:patients,email',
            'address' => 'required|string',
            'age' => 'required|numeric',
            'height' => 'required|numeric',
            'weight' => 'required|numeric',
            'blood_group' => 'required|string',
            'gender' => 'required|string',
            'password' => 'required|min:6|confirmed',
        ], [
            'name.regex' => 'The name must start with a capital letter.',
            'phone.digits' => 'The phone number must be exactly 11 digits.',
        ]);

        $patient = new Patient;
        $patient->name = $req->input('name');
        $patient->phone = $req->input('phone');
        $patient->email = $req->input('email');
        $patient->address = $req->input('address');
        $patient->age = $req->input('age');
        $patient->height = $req->input('height');
        $patient->weight = $req->input('weight');
        $patient->blood_group = $req->input('blood_group');
        $patient->gender = $req->input('gender');
        $patient->previous_record = $req->input('previous_record', 'no');
        $patient->uploaded_record = $req->input('uploaded_record');
        $patient->password = Hash::make($req->input('password'));
        $patient->save();
        return response()->json($patient, 201);
    }
    public function login(Request $req)
    {
        $patient = Patient::where('email', $req->input('email'))->first();
        if (!$patient || !Hash::check($req->input('password'), $patient->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

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

                $path = $request->file('file')->store('health_records', 'public');
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
}