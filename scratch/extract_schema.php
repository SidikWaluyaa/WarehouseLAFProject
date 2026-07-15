<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
foreach ($tables as $t) {
    $tb = (array)$t;
    $table = array_values($tb)[0];
    echo "\nTable: $table\n";
    $cols = \Illuminate\Support\Facades\DB::select("DESCRIBE $table");
    foreach ($cols as $col) {
        echo "  - {$col->Field} ({$col->Type})\n";
    }
}
