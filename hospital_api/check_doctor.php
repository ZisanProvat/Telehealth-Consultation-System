<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'nahar.sabikunlima@gmail.com';
$count = \App\Models\Doctor::where('email', $email)->count();
echo "Count for $email: $count\n";

if ($count > 0) {
    $d = \App\Models\Doctor::where('email', $email)->first();
    echo "ID: {$d->id}, Name: {$d->full_name}\n";
}
