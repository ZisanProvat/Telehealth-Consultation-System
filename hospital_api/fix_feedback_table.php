<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

$table = 'feedback';
if (Schema::hasTable($table)) {
    Schema::table($table, function (Blueprint $table) {
        if (!Schema::hasColumn('feedback', 'name')) {
            $table->string('name')->after('id');
            echo "Added 'name' column.\n";
        }
        if (!Schema::hasColumn('feedback', 'email')) {
            $table->string('email')->after('name');
            echo "Added 'email' column.\n";
        }
        if (!Schema::hasColumn('feedback', 'subject')) {
            $table->string('subject')->after('email');
            echo "Added 'subject' column.\n";
        }
        if (!Schema::hasColumn('feedback', 'message')) {
            $table->text('message')->after('subject');
            echo "Added 'message' column.\n";
        }
    });
} else {
    Schema::create($table, function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('subject');
        $table->text('message');
        $table->timestamps();
    });
    echo "Created 'feedback' table.\n";
}
