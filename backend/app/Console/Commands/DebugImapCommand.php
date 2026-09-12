<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class DebugImapCommand extends Command
{
    protected $signature = 'email:debug-imap';
    protected $description = 'Debug IMAP messages';

    public function handle(): int
    {
        $tenants = ['crm', 'crm_ecogreen'];
        foreach ($tenants as $tenantDb) {
            $this->info("Checking tenant $tenantDb...");
            Config::set('database.connections.tenant.database', $tenantDb);
            Config::set('database.default', 'tenant');
            DB::purge('tenant');
            DB::purge();

            $settings = EmailSetting::all();
            foreach ($settings as $s) {
                $this->info("Setting ID={$s->id}, user_id={$s->user_id}, host={$s->imap_host}, port={$s->imap_port}, enc={$s->imap_encryption}, user={$s->imap_username}, pass_len=" . strlen($s->imap_password) . ", is_imap_enabled={$s->is_imap_enabled}");
            }
        }
        return Command::SUCCESS;
    }
}
