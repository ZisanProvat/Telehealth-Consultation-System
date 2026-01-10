<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\SslCommerzPaymentController;
use App\Http\Controllers\DoctorReportController;
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
Route::get('verify-email', [PatientController::class, 'verifyPatientEmail']);

Route::post('doctor/login', [DoctorController::class, 'login']);
Route::post('doctor/update-password', [DoctorController::class, 'updatePassword']);
Route::post('doctors/{id}/update-profile', [DoctorController::class, 'updateProfile']);
Route::post('doctors', [DoctorController::class, 'store']);
Route::get('doctors', [DoctorController::class, 'index']);
Route::get('doctors/{id}', [DoctorController::class, 'show']);

// Appointment routes
Route::get('appointments', [AppointmentController::class, 'index']);
Route::get('appointments/patient/{patientId}', [AppointmentController::class, 'getPatientAppointments']);
Route::get('appointments/doctor/{doctorId}', [AppointmentController::class, 'getDoctorAppointments']);
Route::post('appointments', [AppointmentController::class, 'store']);
Route::put('appointments/{id}', [AppointmentController::class, 'update']);
Route::delete('appointments/{id}', [AppointmentController::class, 'destroy']);

// Admin routes
Route::post('admin/login', [\App\Http\Controllers\AdminController::class, 'login']);
Route::get('admin/dashboard-stats', [\App\Http\Controllers\AdminController::class, 'getDashboardStats']);
Route::post('admin/update-profile', [\App\Http\Controllers\AdminController::class, 'updateProfile']);
Route::post('admin/change-password', [\App\Http\Controllers\AdminController::class, 'changePassword']);

// Admin Patient Management
Route::get('admin/patients', [\App\Http\Controllers\AdminController::class, 'getPatients']);
Route::delete('admin/patients/{id}', [\App\Http\Controllers\AdminController::class, 'deletePatient']);
Route::post('admin/patients', [\App\Http\Controllers\AdminController::class, 'storePatient']);
Route::put('admin/patients/{id}', [\App\Http\Controllers\AdminController::class, 'updatePatient']);

// Admin Doctor Management
Route::get('admin/doctors', [\App\Http\Controllers\AdminController::class, 'getDoctors']);
Route::post('admin/doctors', [\App\Http\Controllers\AdminController::class, 'storeDoctor']);
Route::put('admin/doctors/{id}', [\App\Http\Controllers\AdminController::class, 'updateDoctor']);
Route::delete('admin/doctors/{id}', [\App\Http\Controllers\AdminController::class, 'deleteDoctor']);

// Admin Appointment Management
Route::get('admin/appointments', [\App\Http\Controllers\AdminController::class, 'getAllAppointments']);
Route::put('admin/appointments/{id}', [\App\Http\Controllers\AdminController::class, 'updateAppointmentStatus']);
Route::delete('admin/appointments/{id}', [\App\Http\Controllers\AdminController::class, 'deleteAppointment']);

// Admin Payment Management
Route::get('admin/payments', [\App\Http\Controllers\AdminController::class, 'getPaymentHistory']);

// Doctor Monthly Reports (Admin only)
Route::get('admin/reports/doctor/{doctorId}/monthly', [DoctorReportController::class, 'getMonthlyReport']);
Route::get('admin/reports/doctors/monthly', [DoctorReportController::class, 'getAllDoctorsReport']);
Route::get('admin/reports/doctor/{doctorId}/yearly', [DoctorReportController::class, 'getYearlyReport']);
Route::post('admin/reports/generate', [DoctorReportController::class, 'generateMonthlyReport']);

// Password Reset Routes
Route::post('forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('reset-password', [PasswordResetController::class, 'reset']);

// Feedback Routes
Route::post('feedback', [FeedbackController::class, 'store']);
Route::get('admin/feedback', [FeedbackController::class, 'index']);
Route::delete('admin/feedback/{id}', [FeedbackController::class, 'destroy']);

// SSLCommerz Routes
Route::post('pay', [SslCommerzPaymentController::class, 'index']);
Route::post('success', [SslCommerzPaymentController::class, 'success'])->name('sslc.success');
Route::post('fail', [SslCommerzPaymentController::class, 'fail'])->name('sslc.failure');
Route::post('cancel', [SslCommerzPaymentController::class, 'cancel'])->name('sslc.cancel');
Route::post('ipn', [SslCommerzPaymentController::class, 'ipn'])->name('sslc.ipn');

Route::get('/debug-admin', function () {
    $admins = \App\Models\Admin::all();
    return response()->json([
        'count' => $admins->count(),
        'admins' => $admins->map(function ($admin) {
            return [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
            ];
        })
    ]);
});
