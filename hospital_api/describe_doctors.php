<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$results = Illuminate\Support\Facades\DB::select('DESCRIBE doctors');
foreach ($results as $col) {
    echo $col->Field . ': ' . $col->Type . ' (' . ($col->Null == 'YES' ? 'NULL' : 'NOT NULL') . ')' . PHP_EOL;
}
