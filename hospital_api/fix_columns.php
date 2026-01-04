<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Illuminate\Support\Facades\DB::statement('ALTER TABLE doctors MODIFY user_id INT(11) NULL');
    Illuminate\Support\Facades\DB::statement('ALTER TABLE doctors MODIFY specialization VARCHAR(100) NULL');
    Illuminate\Support\Facades\DB::statement('ALTER TABLE doctors MODIFY phone VARCHAR(20) NULL');
    echo "Success!";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
