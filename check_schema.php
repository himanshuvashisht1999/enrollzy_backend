<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;
file_put_contents('schema_check.txt', print_r(DB::select('SHOW COLUMNS FROM calling_status'), true));
