<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$results = Illuminate\Support\Facades\DB::select('DESCRIBE doctors');
foreach ($results as $col) {
    printf("%-20s %-10s %s\n", $col->Field, $col->Null, $col->Type);
}
