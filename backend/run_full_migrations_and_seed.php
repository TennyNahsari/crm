<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "════════════════════════════════════════════════════════════\n";
echo "   RUNNING SINGLE DATABASE MIGRATIONS & SEEDERS\n";
echo "════════════════════════════════════════════════════════════\n\n";

try {
    echo "Step 1: Running Fresh Database Migrations...\n";
    Artisan::call('migrate:fresh', [
        '--force' => true,
    ]);
    echo Artisan::output();
    echo "✓ Migrations completed.\n\n";

    echo "Step 2: Running Database Seeder...\n";
    Artisan::call('db:seed', [
        '--force' => true,
    ]);
    echo Artisan::output();

    echo "════════════════════════════════════════════════════════════\n";
    echo "✅ MIGRATION & SEEDING COMPLETED SUCCESSFULLY!\n";
    echo "════════════════════════════════════════════════════════════\n\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
