<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = Illuminate\Support\Facades\Schema::getConnection()->getDoctrineSchemaManager()->listTableColumns('doctors');

foreach ($columns as $column) {
    echo $column->getName() . ': ' . ($column->getNotnull() ? 'NOT NULL' : 'NULL') . PHP_EOL;
}
