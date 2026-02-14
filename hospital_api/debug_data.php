<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

echo "--- APPOINTMENTS STATS ---\n";
$stats = App\Models\Appointment::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
    ->groupBy('status')
    ->get();
foreach ($stats as $stat) {
    echo "Status: '{$stat->status}' -> Count: {$stat->total}\n";
}

echo "\n--- DOCTORS VISITING DAYS ---\n";
$doctors = App\Models\Doctor::whereNotNull('visiting_days')->take(5)->get();
foreach ($doctors as $doc) {
    echo "ID: {$doc->id}, Days: '{$doc->visiting_days}' (Type: " . gettype($doc->visiting_days) . ")\n";
}
