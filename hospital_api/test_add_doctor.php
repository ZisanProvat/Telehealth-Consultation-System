<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $doctor = new \App\Models\Doctor();
    $doctor->full_name = "Sabikun Nahar Lima";
    $doctor->email = "nahar.sabikunlima@gmail.com";
    $doctor->password = \Illuminate\Support\Facades\Hash::make("123456");
    $doctor->phone = "01789451876";
    $doctor->specialization = "Cardiologist";
    $doctor->qualification = "MBBS";
    $doctor->experience = "8 Years";
    $doctor->designation = "Consultant";
    $doctor->bmdc_no = "A-9087";
    $doctor->visiting_days = "Friday, Sunday";
    $doctor->visiting_hours = "9 AM to 12 PM";
    $doctor->save();
    echo "Doctor saved successfully with ID: " . $doctor->id;
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
