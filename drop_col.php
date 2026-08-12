<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('calling_status', function (Blueprint $table) {
    if (Schema::hasColumn('calling_status', 'calling_action_id')) {
        $table->dropColumn('calling_action_id');
    }
});
echo "Column dropped if existed.\n";
