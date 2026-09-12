<?php
require __DIR__ . '/backend/vendor/autoload.php';
$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\EmailSetting;
use App\Services\ImapSyncService;

Config::set('database.connections.tenant.database', 'crm_ecogreen');
Config::set('database.default', 'tenant');
DB::purge('tenant');
DB::purge();

$setting = EmailSetting::first();
echo "Setting:\n";
print_r($setting ? $setting->toArray() : null);

if ($setting) {
    $svc = new ImapSyncService();
    echo "\nTest Connection:\n";
    print_r($svc->testConnection($setting));
    echo "\nSync Result:\n";
    print_r($svc->sync($setting));
}
