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
        if (Schema::hasTable('feedback')) {
            Schema::table('feedback', function (Blueprint $table) {
                if (!Schema::hasColumn('feedback', 'name')) {
                    $table->string('name')->after('id');
                }
                if (!Schema::hasColumn('feedback', 'email')) {
                    $table->string('email')->after('name');
                }
                if (!Schema::hasColumn('feedback', 'subject')) {
                    $table->string('subject')->after('email');
                }
                if (!Schema::hasColumn('feedback', 'message')) {
                    $table->text('message')->after('subject');
                }
            });
        } else {
            Schema::create('feedback', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email');
                $table->string('subject');
                $table->text('message');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
