<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('doctor_monthly_reports')) {
            return;
        }
        Schema::create('doctor_monthly_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->integer('month'); // 1-12
            $table->integer('year'); // e.g., 2026

            // Metrics
            $table->integer('days_assigned')->default(0);
            $table->integer('days_absent')->default(0);
            $table->integer('total_patients')->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0);

            // Appointment counts
            $table->integer('completed_appointments')->default(0);
            $table->integer('scheduled_appointments')->default(0);
            $table->integer('cancelled_appointments')->default(0);
            $table->integer('no_show_appointments')->default(0);

            // Calculated metrics
            $table->decimal('average_patients_per_day', 5, 2)->default(0);
            $table->decimal('completion_rate', 5, 2)->default(0); // percentage

            $table->timestamps();

            // Unique constraint: one report per doctor per month
            $table->unique(['doctor_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_monthly_reports');
    }
};
