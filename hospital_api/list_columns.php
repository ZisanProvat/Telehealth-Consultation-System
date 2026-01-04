<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (Illuminate\Support\Facades\Schema::getColumnListing('patients') as $column) {
    echo $column . PHP_EOL;
}
