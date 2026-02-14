<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Doctors Columns ---\n";
$results = Illuminate\Support\Facades\DB::select('DESCRIBE doctors');
foreach ($results as $col) {
    echo $col->Field . ': ' . $col->Type . ' (' . ($col->Null == 'YES' ? 'NULL' : 'NOT NULL') . ")\n";
}

echo "\n--- Last 5 Doctors ---\n";
$doctors = \App\Models\Doctor::latest()->take(5)->get();
foreach ($doctors as $d) {
    echo "ID: {$d->id}, Name: {$d->full_name}, Email: {$d->email}\n";
}
