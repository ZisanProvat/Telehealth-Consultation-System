<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$output = "";
$results = Illuminate\Support\Facades\DB::select('DESCRIBE doctors');
foreach ($results as $col) {
    $output .= sprintf("%-20s %-10s %s\n", $col->Field, $col->Null, $col->Type);
}
file_put_contents('cols_final.txt', $output);
