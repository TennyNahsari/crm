<?php
file_put_contents(__DIR__ . '/output.log', "Starting script...\n");

require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EmailSetting;
use App\Models\Email;
use App\Models\Customer;
use App\Services\ImapSyncService;

function log_out($msg) {
    file_put_contents(__DIR__ . '/output.log', print_r($msg, true) . "\n", FILE_APPEND);
    echo $msg . "\n";
}

log_out("PHP imap_open exists: " . (function_exists('imap_open') ? 'YES' : 'NO'));

$s = EmailSetting::first();
if (!$s) {
    log_out("NO EMAIL SETTINGS FOUND IN DEFAULT DB!");
    exit;
}

log_out("Found EmailSetting: ID={$s->id}, Host={$s->imap_host}, User={$s->imap_username}, Enabled={$s->is_imap_enabled}");

$svc = new ImapSyncService();
log_out("Testing connection...");
$resTest = $svc->testConnection($s);
log_out($resTest);

log_out("Testing sync...");
$resSync = $svc->sync($s);
log_out($resSync);

log_out("Done.");
