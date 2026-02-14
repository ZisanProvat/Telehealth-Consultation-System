<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['doctors', 'patients'];
foreach ($tables as $table) {
    echo "--- $table ---" . PHP_EOL;
    $results = Illuminate\Support\Facades\DB::select("DESCRIBE $table");
    foreach ($results as $col) {
        echo $col->Field . ': ' . $col->Type . ' (' . ($col->Null == 'YES' ? 'NULL' : 'NOT NULL') . ')' . PHP_EOL;
    }
}
