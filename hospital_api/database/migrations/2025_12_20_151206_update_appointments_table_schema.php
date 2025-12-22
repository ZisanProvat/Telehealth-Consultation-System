<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Doctor information
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->string('doctor_name')->nullable();
            
            // Patient information
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->string('patient_name')->nullable();
            
            // Appointment details
            $table->date('appointment_date')->nullable();
            $table->time('appointment_time')->nullable();
            $table->string('day')->nullable()->comment('Day of week (Monday, Tuesday, etc.)');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('scheduled')->comment('scheduled, completed, cancelled, no-show');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'doctor_id',
                'doctor_name',
                'patient_id',
                'patient_name',
                'appointment_date',
                'appointment_time',
                'day',
                'reason',
                'notes',
                'status'
            ]);
        });
    }
};
