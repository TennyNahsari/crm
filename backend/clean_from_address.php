<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

foreach (['crm', 'crm_ecogreen'] as $db) {
    Config::set('database.connections.tenant.database', $db);
    Config::set('database.default', 'tenant');
    DB::purge('tenant');
    DB::purge();

    $settings = DB::table('email_settings')->get();
    foreach ($settings as $s) {
        if (!empty($s->mail_username)) {
            DB::table('email_settings')->where('id', $s->id)->update([
                'mail_from_address' => $s->mail_username
            ]);
            echo "DB [{$db}] ID {$s->id} updated mail_from_address to match username: {$s->mail_username}\n";
        }
    }
}

echo "All DBs aligned!\n";
