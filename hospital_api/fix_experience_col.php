<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Illuminate\Support\Facades\DB::statement('ALTER TABLE doctors MODIFY experience VARCHAR(50) NULL');
    echo "Success!";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
