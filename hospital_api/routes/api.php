<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AppointmentController;
use App\Models\User;
use App\Models\Doctor;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Example Protected Route (optional)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('register', [PatientController::class, 'register']);
Route::post('login', [PatientController::class, 'login']);
Route::post('patients/{id}/upload-record', [PatientController::class, 'uploadHealthRecord']);
Route::post('patients/{id}/update-profile', [PatientController::class, 'updateProfile']);
Route::post('patients/{id}/change-password', [PatientController::class, 'changePassword']);
Route::delete('patients/{id}/health-records', [PatientController::class, 'deleteHealthRecord']);
Route::get('patients/{id}', [PatientController::class, 'show']);

Route::post('doctor/login', [DoctorController::class, 'login']);
Route::post('doctor/update-password', [DoctorController::class, 'updatePassword']);
Route::post('doctors', [DoctorController::class, 'store']);
Route::get('doctors', [DoctorController::class, 'index']);
Route::get('doctors/{id}', [DoctorController::class, 'show']);

// Appointment routes
Route::get('appointments', [AppointmentController::class, 'index']);
Route::get('appointments/patient/{patientId}', [AppointmentController::class, 'getPatientAppointments']);
Route::post('appointments', [AppointmentController::class, 'store']);
Route::put('appointments/{id}', [AppointmentController::class, 'update']);
Route::delete('appointments/{id}', [AppointmentController::class, 'destroy']);
