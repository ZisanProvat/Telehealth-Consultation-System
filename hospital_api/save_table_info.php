<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$output = "";
$tables = ['doctors', 'patients'];
foreach ($tables as $table) {
    $output .= "--- $table ---\n";
    $results = Illuminate\Support\Facades\DB::select("DESCRIBE $table");
    foreach ($results as $col) {
        $output .= $col->Field . ': ' . $col->Type . ' (' . ($col->Null == 'YES' ? 'NULL' : 'NOT NULL') . ")\n";
    }
}
file_put_contents('table_info.txt', $output);
