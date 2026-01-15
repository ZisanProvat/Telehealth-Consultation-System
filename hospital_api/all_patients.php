<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Patient;

$all = Patient::all();
echo "Total Patients in Table: " . $all->count() . "\n";
foreach ($all as $p) {
    echo "- ID {$p->id}: {$p->name}\n";
}
