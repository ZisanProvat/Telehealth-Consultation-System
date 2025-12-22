<?php
namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
    // GET doctors
    public function index()
    {
        $doctors = Doctor::all();
        
        // Map database fields to frontend expected fields
        return $doctors->map(function($doctor) {
            return [
                'id' => $doctor->id,
                'name' => $doctor->full_name,
                'specialist' => $doctor->specialization,
                'photo' => $doctor->photo,
                'email' => $doctor->email,
                'phone' => $doctor->phone,
                'designation' => $doctor->designation,
                'qualification' => $doctor->qualification,
                'experience' => $doctor->experience,
                'bmdc_no' => $doctor->bmdc_no,
                'visiting_hours' => $doctor->visiting_hours,
                'visiting_days' => $doctor->visiting_days,
                'description' => $doctor->description,
            ];
        });
    }
    // GET single doctor by ID
    public function show($id)
    {
        $doctor = Doctor::findOrFail($id);
        
        // Map database fields to frontend expected fields
        return [
            'id' => $doctor->id,
            'name' => $doctor->full_name,
            'specialist' => $doctor->specialization,
            'photo' => $doctor->photo,
            'email' => $doctor->email,
            'phone' => $doctor->phone,
            'designation' => $doctor->designation,
            'qualification' => $doctor->qualification,
            'experience' => $doctor->experience,
            'bmdc_no' => $doctor->bmdc_no,
            'visiting_hours' => $doctor->visiting_hours,
            'visiting_days' => $doctor->visiting_days,
            'description' => $doctor->description,
        ];
    }
    // POST store doctor
    public function store(Request $req)
    {
        $doctor = new Doctor;

        $doctor->user_id = $req->input('user_id'); // REQUIRED
        $doctor->full_name = $req->input('full_name');
        $doctor->email = $req->input('email');
        $doctor->phone = $req->input('phone');
        $doctor->designation = $req->input('designation');
        $doctor->specialization = $req->input('specialization');
        $doctor->qualification = $req->input('qualification');
        $doctor->experience = $req->input('experience');
        $doctor->bmdc_no = $req->input('bmdc_no');
        $doctor->visiting_hours = $req->input('visiting_hours');
        $doctor->visiting_days = $req->input('visiting_days');
        $doctor->description = $req->input('description');
        $doctor->photo = $req->input('photo');
        $doctor->password = Hash::make($req->input('password'));

        $doctor->save();

       return response()->json([
            'message' => 'Doctor created successfully',
            'data' => $doctor
        ], 201);
    }

    public function login(Request $req)
    {
        $doctor = Doctor::where('email', $req->input('email'))->first();
        if (!$doctor || !Hash::check($req->input('password'), $doctor->password)) {
             return response()->json(['message' => 'Invalid credentials'], 401);
        }
        
        // Add role to doctor object
        $doctor->role = 'doctor';
        
        return response()->json([
            'doctor' => $doctor,
            'token' => 'doctor_token_' . $doctor->id,
            'message' => 'Login successful'
        ], 200);
    }

    public function updatePassword(Request $req)
    {
        try {
            $doctor = Doctor::findOrFail($req->input('doctor_id'));
            
            // Verify old password
            if (!Hash::check($req->input('old_password'), $doctor->password)) {
                return response()->json(['message' => 'Old password is incorrect'], 401);
            }
            
            // Update password
            $doctor->password = Hash::make($req->input('new_password'));
            $doctor->save();
            
            return response()->json(['message' => 'Password updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update password'], 500);
        }
    }
    
}
