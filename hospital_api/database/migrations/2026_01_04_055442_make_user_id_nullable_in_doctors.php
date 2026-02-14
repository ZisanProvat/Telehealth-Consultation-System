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
        Illuminate\Support\Facades\DB::statement('ALTER TABLE doctors MODIFY user_id INT(11) NULL');
        Illuminate\Support\Facades\DB::statement('ALTER TABLE doctors MODIFY specialization VARCHAR(100) NULL');
        Illuminate\Support\Facades\DB::statement('ALTER TABLE doctors MODIFY phone VARCHAR(20) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Illuminate\Support\Facades\DB::statement('ALTER TABLE doctors MODIFY user_id INT(11) NOT NULL');
        Illuminate\Support\Facades\DB::statement('ALTER TABLE doctors MODIFY specialization VARCHAR(100) NOT NULL');
        Illuminate\Support\Facades\DB::statement('ALTER TABLE doctors MODIFY phone VARCHAR(20) NOT NULL');
    }
};
