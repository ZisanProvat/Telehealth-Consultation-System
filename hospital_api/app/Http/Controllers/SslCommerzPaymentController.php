<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DGvai\SSLCommerz\SSLCommerz;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SslCommerzPaymentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $appointmentId = $request->appointment_id;
            $appointment = Appointment::findOrFail($appointmentId);

            // Use provided payment method if available
            $paymentMethod = $request->payment_method; // e.g., 'bkash', 'nagad'

            // Reuse existing transaction ID if possible, otherwise generate a new one
            $tran_id = $appointment->transaction_id ?: ('SSLC_' . $appointment->id . '_' . uniqid());

            // Update appointment before redirecting
            $appointment->update([
                'transaction_id' => $tran_id,
                'payment_method' => $paymentMethod ?: ($appointment->payment_method ?: 'SSLCommerz'),
            ]);

            $sslc = new SSLCommerz();

            $patient = $appointment->patient;
            $email = $patient->email ?? 'no-email@example.com';
            $phone = $patient->phone ?? '01700000000';
            $address = $patient->address ?? 'Dhaka, Bangladesh';

            $sslc->amount($appointment->amount)
                ->trxid($tran_id)
                ->product('Medical Consultation')
                ->customer($appointment->patient_name, $email, $address, $phone);

            // Fetch the payment result
            $paymentResponse = $sslc->make_payment();

            // If it's an axios/ajax request, we need to return the URL as JSON
            if ($request->wantsJson() || $request->ajax()) {
                if (method_exists($paymentResponse, 'getTargetUrl')) {
                    return response()->json(['data' => $paymentResponse->getTargetUrl()]);
                }
                return response()->json(['data' => $paymentResponse]);
            }

            return $paymentResponse;
        } catch (\Exception $e) {
            Log::error('SSLCommerz Initiation Error: ' . $e->getMessage(), [
                'exception' => $e,
                'appointment_id' => $request->appointment_id
            ]);
            return response()->json([
                'message' => 'Failed to initiate payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function success(Request $request)
    {
        Log::info('--- SSLCOMMERZ SUCCESS START ---');
        Log::info('Request Data:', $request->all());

        $validate = SSLCommerz::validate_payment($request->all());
        Log::info('Package Validation Result: ' . ($validate ? 'TRUE' : 'FALSE'));

        if (!$validate && in_array($request->status, ['VALIDATED', 'VALID'])) {
            Log::info('Performing Manual Validation Fallback...');
            $appointment = Appointment::where('transaction_id', $request->tran_id)->first();

            if ($appointment) {
                Log::info('Appointment Found in DB: ' . $appointment->id . ' | Amount: ' . $appointment->amount);
                if ((float) $appointment->amount === (float) $request->amount) {
                    $validate = true;
                    Log::info('Manual Validation: SUCCESS');
                } else {
                    Log::info('Manual Validation: FAILED (Amount mismatch)');
                }
            } else {
                Log::info('Manual Validation: FAILED (Appointment not found for ' . $request->tran_id . ')');
            }
        }

        if ($validate) {
            Log::info('Proceeding with Appointment Update...');
            $tran_id = $request->tran_id;

            DB::beginTransaction();
            try {
                $appointment = Appointment::where('transaction_id', $tran_id)->first();

                if (!$appointment && strpos($tran_id, 'SSLC_') === 0) {
                    $parts = explode('_', $tran_id);
                    if (isset($parts[1]) && is_numeric($parts[1])) {
                        $appointment = Appointment::find($parts[1]);
                        Log::info('Appointment Found via Fallback Parse: ' . ($appointment ? $appointment->id : 'NONE'));
                    }
                }

                if ($appointment) {
                    $appointment->update([
                        'status' => 'scheduled',
                        'payment_status' => 'paid',
                        'payment_method' => $request->card_issuer ?: $appointment->payment_method,
                        'payment_details' => $request->all(),
                    ]);
                    DB::commit();
                    Log::info('Appointment ' . $appointment->id . ' updated successfully. Redirecting to success dashboard.');
                    return redirect('http://localhost:3000/dashboard?payment=success&tran_id=' . $tran_id);
                } else {
                    Log::error('CRITICAL: Validation succeeded but appointment NOT found for update!');
                    DB::commit();
                }
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('UPDATE ERROR: ' . $e->getMessage());
            }
        }

        Log::error('--- SSLCOMMERZ SUCCESS FAILED FINAL ---');
        return redirect('http://localhost:3000/dashboard?payment=failed');
    }

    public function fail(Request $request)
    {
        Log::warning('SSLCommerz Payment Failed URL Hit:', $request->all());
        return redirect('http://localhost:3000/dashboard?payment=failed');
    }

    public function cancel(Request $request)
    {
        Log::info('SSLCommerz Payment Cancelled URL Hit:', $request->all());
        return redirect('http://localhost:3000/dashboard?payment=cancelled');
    }

    public function ipn(Request $request)
    {
        Log::info('SSLCommerz IPN Received:', $request->all());

        $validate = SSLCommerz::validate_payment($request->all());

        if ($validate) {
            $tran_id = $request->tran_id;
            $appointment = Appointment::where('transaction_id', $tran_id)->first();

            // Fallback: Parse ID from tran_id
            if (!$appointment && strpos($tran_id, 'SSLC_') === 0) {
                $parts = explode('_', $tran_id);
                if (isset($parts[1]) && is_numeric($parts[1])) {
                    $appointment = Appointment::find($parts[1]);
                }
            }

            if ($appointment && $appointment->payment_status !== 'paid') {
                $appointment->update([
                    'status' => 'scheduled',
                    'payment_status' => 'paid',
                    'payment_details' => $request->all(),
                ]);
            }
        }
    }
}
