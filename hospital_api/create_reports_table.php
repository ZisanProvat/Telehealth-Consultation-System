<?php
/**
 * Manual script to create doctor_monthly_reports table
 * Run this if migration fails
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    // Check if table already exists
    if (Schema::hasTable('doctor_monthly_reports')) {
        echo "Table 'doctor_monthly_reports' already exists.\n";
        exit(0);
    }

    // Create the table using raw SQL
    DB::statement("
        CREATE TABLE doctor_monthly_reports (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            doctor_id BIGINT UNSIGNED NOT NULL,
            month INT NOT NULL,
            year INT NOT NULL,
            days_assigned INT DEFAULT 0,
            days_absent INT DEFAULT 0,
            total_patients INT DEFAULT 0,
            total_revenue DECIMAL(10, 2) DEFAULT 0,
            completed_appointments INT DEFAULT 0,
            scheduled_appointments INT DEFAULT 0,
            cancelled_appointments INT DEFAULT 0,
            no_show_appointments INT DEFAULT 0,
            average_patients_per_day DECIMAL(5, 2) DEFAULT 0,
            completion_rate DECIMAL(5, 2) DEFAULT 0,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            UNIQUE KEY unique_doctor_month_year (doctor_id, month, year)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "✓ Table 'doctor_monthly_reports' created successfully!\n";

} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
    exit(1);
}
