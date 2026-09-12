<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailSetting;
use App\Services\ImapSyncService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncImapEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:sync-imap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize incoming emails via IMAP protocol into CRM Customer Interactions for all tenants';

    /**
     * Execute the console command.
     */
    public function handle(ImapSyncService $imapService): int
    {
        $this->info('Starting IMAP Email Sync for all tenant databases...');
        
        $tenants = ['crm', 'crm_ecogreen'];

        foreach ($tenants as $tenantDb) {
            try {
                $this->info("Processing tenant database [{$tenantDb}]...");
                
                Config::set('database.connections.tenant.database', $tenantDb);
                Config::set('database.default', 'tenant');
                DB::purge('tenant');
                DB::purge();

                $settings = EmailSetting::where('is_imap_enabled', true)->get();

                if ($settings->isEmpty()) {
                    $this->info("No active IMAP email settings found for tenant {$tenantDb}.");
                    continue;
                }

                foreach ($settings as $setting) {
                    $result = $imapService->sync($setting);
                    $this->info("Synced {$result['synced']} email(s) for User ID: {$setting->user_id}");
                }
            } catch (\Exception $e) {
                $this->error("Error syncing IMAP for tenant [{$tenantDb}]: " . $e->getMessage());
                Log::error("IMAP sync command error on {$tenantDb}: " . $e->getMessage());
            }
        }

        $this->info('IMAP Email Sync completed.');
        return Command::SUCCESS;
    }
}
