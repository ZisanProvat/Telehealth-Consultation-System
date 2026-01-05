<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Dgvai\SslCommerz\SslCommerz;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SslCommerzPaymentController extends Controller
{
    public function index(Request $request)
    {
        $appointmentId = $request->appointment_id;
        $appointment = Appointment::findOrFail($appointmentId);

        // Use provided payment method if available
        $paymentMethod = $request->payment_method; // e.g., 'bkash', 'nagad'

        $tran_id = 'SSLC_' . $appointment->id . '_' . uniqid();

        // Update appointment with transaction ID before redirecting
        $appointment->update([
            'transaction_id' => $tran_id,
            'payment_method' => $paymentMethod ?: 'SSLCommerz',
        ]);

        $sslc = new SslCommerz();

        $patient = $appointment->patient;
        $email = $patient->email ?? 'no-email@example.com';
        $phone = $patient->phone ?? '01700000000';
        $address = $patient->address ?? 'Dhaka, Bangladesh';

        $sslc->amount($appointment->amount)
            ->trxid($tran_id)
            ->product('Medical Consultation')
            ->customer($appointment->patient_name, $email, $address, $phone);

        // Optional: Pre-select gateway if supported by SSLCommerz API
        // This depends on the specific package version and SSLCommerz configuration
        // In some cases, we pass it via additional parameters
        if ($paymentMethod && in_array($paymentMethod, ['bkash', 'nagad', 'rocket', 'visa', 'mastercard'])) {
            // Note: SSLCommerz uses specific internal names for gateways
            // This is just a placeholder logic to illustrate "real project" steps
            // $sslc->setAdditionalParams(['multi_card_name' => $paymentMethod]);
        }

        return $sslc->make_payment();
    }

    public function success(Request $request)
    {
        Log::info('SSLCommerz Success Callback:', $request->all());

        $validate = SslCommerz::validate_payment($request->all());

        if ($validate) {
            $tran_id = $request->tran_id;

            DB::beginTransaction();
            try {
                $appointment = Appointment::where('transaction_id', $tran_id)->first();
                if ($appointment) {
                    $appointment->update([
                        'payment_status' => 'paid',
                        'payment_method' => $request->card_issuer ?: $appointment->payment_method,
                        'payment_details' => $request->all(),
                    ]);
                }
                DB::commit();
                return redirect('http://localhost:3000/dashboard?payment=success&tran_id=' . $tran_id);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Payment Success Update Failed: ' . $e->getMessage());
            }
        }

        return redirect('http://localhost:3000/dashboard?payment=failed');
    }

    public function fail(Request $request)
    {
        Log::warning('SSLCommerz Payment Failed:', $request->all());
        return redirect('http://localhost:3000/dashboard?payment=failed');
    }

    public function cancel(Request $request)
    {
        Log::info('SSLCommerz Payment Cancelled:', $request->all());
        return redirect('http://localhost:3000/dashboard?payment=cancelled');
    }

    public function ipn(Request $request)
    {
        Log::info('SSLCommerz IPN Received:', $request->all());

        $validate = SslCommerz::validate_payment($request->all());

        if ($validate) {
            $tran_id = $request->tran_id;
            $appointment = Appointment::where('transaction_id', $tran_id)->first();

            if ($appointment && $appointment->payment_status !== 'paid') {
                $appointment->update([
                    'payment_status' => 'paid',
                    'payment_details' => $request->all(),
                ]);
            }
        }
    }
}
