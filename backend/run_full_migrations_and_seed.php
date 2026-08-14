<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "════════════════════════════════════════════════════════════\n";
echo "   RUNNING MULTI-TENANT MIGRATIONS & SEEDERS\n";
echo "════════════════════════════════════════════════════════════\n\n";

try {
    // 1. Run Master Migrations
    echo "Step 1: Running Master Database Migrations...\n";
    Artisan::call('migrate', [
        '--database' => 'master',
        '--path' => 'database/migrations/master',
        '--force' => true,
    ]);
    echo "✓ Master migrations completed.\n\n";

    // List of tenant databases
    $tenantDatabases = ['crm', 'crm_ecogreen'];

    foreach ($tenantDatabases as $tenantDb) {
        echo "Step 2: Fresh Migrating Tenant Database [{$tenantDb}]...\n";
        
        // Set tenant DB connection
        Config::set('database.connections.tenant.database', $tenantDb);
        Config::set('database.default', 'tenant');
        DB::purge('tenant');
        DB::purge();
        DB::reconnect('tenant');
        DB::reconnect();

        // Wipe all tables in tenant DB for clean migration
        Schema::connection('tenant')->dropAllTables();
        echo "   ✓ Tenant database wiped clean\n";

        // 2a. Run tenant specific migration (user_profiles)
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);
        echo "   ✓ Tenant base table (user_profiles) created\n";

        // 2b. Run main application migrations on tenant connection
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--force' => true,
        ]);
        echo "   ✓ Tenant app migrations completed\n\n";
    }

    // 3. Run Seeder
    echo "Step 3: Running Multi-Tenant Seeder...\n";
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
