<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasTenantUser;
use App\Models\EmailSetting;
use App\Models\Customer;
use App\Models\Interaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class EmailController extends Controller
{
    use HasTenantUser;

    public function send(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'to' => 'required|string',
            'subject' => 'required|string',
            'body' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240', // Max 10MB per file
        ]);

        $userProfile = $this->getCurrentUserProfile();
        $emailSetting = EmailSetting::where('user_id', $userProfile->id)->first();

        if (!$emailSetting || empty($emailSetting->mail_host) || empty($emailSetting->mail_username)) {
            return response()->json([
                'message' => 'Silakan konfigurasi Pengaturan Email (SMTP) Anda terlebih dahulu.'
            ], 400);
        }

        // Clean single from email address (prevent comma-separated multiple emails causing 550 spoofed email error)
        $rawFrom = $emailSetting->mail_from_address ?: $emailSetting->mail_username;
        $fromAddress = trim(explode(',', $rawFrom)[0]);
        $fromName = $emailSetting->mail_from_name ?: 'FlowCRM';

        // Configure mail settings dynamically
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', $emailSetting->mail_host);
        Config::set('mail.mailers.smtp.port', $emailSetting->mail_port);
        Config::set('mail.mailers.smtp.username', $emailSetting->mail_username);
        Config::set('mail.mailers.smtp.password', $emailSetting->mail_password);
        Config::set('mail.mailers.smtp.encryption', $emailSetting->mail_encryption);
        Config::set('mail.from.address', $fromAddress);
        Config::set('mail.from.name', $fromName);

        // Purge mailer cache so Laravel applies dynamic configuration
        Mail::purge('smtp');

        try {
            // Send email with HTML support and attachments
            Mail::send([], [], function ($message) use ($validated, $fromAddress, $fromName, $request) {
                $message->to($validated['to'])
                        ->subject($validated['subject'])
                        ->from($fromAddress, $fromName)
                        ->html($validated['body']);
                
                // Attach files if present
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $message->attach($file->getRealPath(), [
                            'as' => $file->getClientOriginalName(),
                            'mime' => $file->getMimeType(),
                        ]);
                    }
                }
            });

            // Log interaction
            Interaction::create([
                'customer_id' => $validated['customer_id'],
                'interaction_type' => 'email_outbound',
                'channel' => 'email',
                'subject' => $validated['subject'],
                'content' => $validated['body'],
                'summary' => 'Email sent: ' . $validated['subject'],
                'interaction_at' => now(),
                'created_by_type' => 'user',
                'created_by_user_id' => $userProfile->id,
                'lead_status_snapshot_id' => Customer::find($validated['customer_id'])->lead_status_id,
            ]);

            return response()->json([
                'message' => 'Email sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }
}
