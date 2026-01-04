<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$table = 'feedback';
if (Schema::hasTable($table)) {
    echo "Table '$table' exists.\n";
    $columns = Schema::getColumnListing($table);
    print_r($columns);
} else {
    echo "Table '$table' does not exist.\n";
}
