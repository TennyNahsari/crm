<?php

namespace App\Jobs;

use App\Models\EmailSetting;
use App\Services\ImapSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncImapEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $emailSettingId;

    /**
     * Create a new job instance.
     */
    public function __construct($emailSettingId = null)
    {
        $this->emailSettingId = $emailSettingId;
    }

    /**
     * Execute the job.
     */
    public function handle(ImapSyncService $imapService): void
    {
        Log::info('Starting SyncImapEmailsJob...');

        try {
            if ($this->emailSettingId) {
                $settings = EmailSetting::where('id', $this->emailSettingId)
                    ->where('is_imap_enabled', true)
                    ->get();
            } else {
                $settings = EmailSetting::where('is_imap_enabled', true)->get();
            }

            foreach ($settings as $setting) {
                $result = $imapService->sync($setting);
                Log::info("Synced {$result['synced']} email(s) for User ID: {$setting->user_id}");
            }
        } catch (\Exception $e) {
            Log::error("Failed IMAP sync: " . $e->getMessage());
        }
    }
}
