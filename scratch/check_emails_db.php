<?php
$out = "";
function p($line) {
    global $out;
    $out .= $line . "\n";
}

require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

foreach (['crm', 'crm_ecogreen'] as $dbName) {
    p("=== DB: $dbName ===");
    Config::set('database.connections.tenant.database', $dbName);
    Config::set('database.default', 'tenant');
    DB::purge('tenant');
    DB::purge();

    $settings = DB::table('email_settings')->get();
    p("EmailSettings count: " . count($settings));
    foreach ($settings as $s) {
        p(" - ID: {$s->id}, User: {$s->user_id}, Host: {$s->imap_host}, Username: {$s->imap_username}, Enabled: " . ($s->is_imap_enabled ? 'TRUE' : 'FALSE'));
    }

    $emails = DB::table('emails')->get();
    p("Emails count: " . count($emails));
    foreach ($emails as $e) {
        p(" - Email ID: {$e->id}, MsgID: {$e->message_id}, From: {$e->from_email}, Subject: {$e->subject}, CustomerID: {$e->customer_id}");
    }

    $customers = DB::table('customers')->get();
    p("Customers count: " . count($customers));
    foreach ($customers as $c) {
        p(" - Customer ID: {$c->id}, Name: {$c->name}, Email: {$c->email}");
    }
}

file_put_contents(__DIR__ . '/db_report.txt', $out);
echo "DONE REPORT\n";
