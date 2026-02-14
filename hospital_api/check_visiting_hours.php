<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Doctor;

$doctors = Doctor::all();
foreach ($doctors as $d) {
    echo "ID: {$d->id} | Name: {$d->name} | Hours: '{$d->visiting_hours}'\n";
}
