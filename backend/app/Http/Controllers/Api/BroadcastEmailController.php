<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasTenantUser;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Contact;
use App\Models\Area;
use App\Models\EmailSetting;
use App\Models\Interaction;
use App\Models\BroadcastEmailHistory;
use App\Models\BroadcastEmailDraft;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class BroadcastEmailController extends Controller
{
    use HasTenantUser;

    public function getRecipients(Request $request)
    {
        $request->validate([
            'filter_type' => 'required|in:all,area',
            'area_id' => 'nullable|required_if:filter_type,area|exists:areas,id'
        ]);

        $user = $this->getCurrentUserProfile();
        $emails = [];
        $query = Customer::with('contacts');

        // Role-based filtering: sales only see their assigned customers
        if ($user->role !== 'admin') {
            $query->where('assigned_sales_id', $user->id);
        }

        if ($request->filter_type === 'area') {
            $query->where('area_id', $request->area_id);
        }

        $customers = $query->get();

        foreach ($customers as $customer) {
            // Add company email
            if ($customer->email) {
                $emails[] = [
                    'email' => $customer->email,
                    'name' => $customer->company,
                    'type' => 'Company'
                ];
            }

            // Add all PIC emails
            foreach ($customer->contacts as $contact) {
                if ($contact->email) {
                    $emails[] = [
                        'email' => $contact->email,
                        'name' => $contact->name,
                        'type' => 'PIC - ' . $customer->company
                    ];
                }
            }
        }

        return response()->json([
            'recipients' => $emails,
            'total' => count($emails)
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'filter_type' => 'required|in:all,area',
            'area_id' => 'nullable|exists:areas,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240', // Max 10MB per file
        ]);

        $user = $this->getCurrentUserProfile();
        $emailSetting = EmailSetting::where('user_id', $user->id)->first();

        if (!$emailSetting || empty($emailSetting->mail_host) || empty($emailSetting->mail_username)) {
            return response()->json([
                'message' => 'Silakan konfigurasi Pengaturan Email (SMTP) Anda terlebih dahulu.'
            ], 400);
        }

        // Clean single from email address (prevent 550 spoofed email error)
        $rawFrom = $emailSetting->mail_from_address ?: $emailSetting->mail_username;
        $fromAddress = trim(explode(',', $rawFrom)[0]);
        $fromName = $emailSetting->mail_from_name ?: 'FlowCRM';

        // Configure mail
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', $emailSetting->mail_host);
        Config::set('mail.mailers.smtp.port', $emailSetting->mail_port);
        Config::set('mail.mailers.smtp.username', $emailSetting->mail_username);
        Config::set('mail.mailers.smtp.password', $emailSetting->mail_password);
        Config::set('mail.mailers.smtp.encryption', $emailSetting->mail_encryption);
        Config::set('mail.from.address', $fromAddress);
        Config::set('mail.from.name', $fromName);
        Mail::purge('smtp');

        // Get recipients with role-based filtering
        $query = Customer::with('contacts');
        
        // Role-based filtering: sales only see their assigned customers
        if ($user->role !== 'admin') {
            $query->where('assigned_sales_id', $user->id);
        }
        
        if ($request->filter_type === 'area' && $request->area_id) {
            $query->where('area_id', $request->area_id);
        }

        $customers = $query->get();
        $sentCount = 0;
        $failedEmails = [];
        $allRecipients = [];

        foreach ($customers as $customer) {
            $emailsSent = [];

            // Send to company email
            if ($customer->email) {
                try {
                    Mail::send([], [], function ($message) use ($customer, $request, $fromAddress, $fromName) {
                        $message->to($customer->email)
                                ->from($fromAddress, $fromName)
                                ->subject($request->subject)
                                ->html($request->body);
                        
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
                    $emailsSent[] = $customer->email;
                    $allRecipients[] = $customer->email;
                    $sentCount++;
                } catch (\Exception $e) {
                    Log::error("Broadcast email error for {$customer->email}: " . $e->getMessage());
                    $failedEmails[] = $customer->email . ' (' . $e->getMessage() . ')';
                }
            }

            // Send to all PICs
            foreach ($customer->contacts as $contact) {
                if ($contact->email) {
                    try {
                        Mail::send([], [], function ($message) use ($contact, $request, $fromAddress, $fromName) {
                            $message->to($contact->email)
                                    ->from($fromAddress, $fromName)
                                    ->subject($request->subject)
                                    ->html($request->body);
                            
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
                        $emailsSent[] = $contact->email;
                        $allRecipients[] = $contact->email;
                        $sentCount++;
                    } catch (\Exception $e) {
                        Log::error("Broadcast email error for PIC {$contact->email}: " . $e->getMessage());
                        $failedEmails[] = $contact->email . ' (' . $e->getMessage() . ')';
                    }
                }
            }

            // Log interaction for this customer
            if (!empty($emailsSent)) {
                Interaction::create([
                    'customer_id' => $customer->id,
                    'interaction_type' => 'email_outbound',
                    'channel' => 'email',
                    'subject' => $request->subject,
                    'content' => $request->body,
                    'summary' => "Broadcast Email to: " . implode(', ', $emailsSent),
                    'interaction_at' => now(),
                    'created_by_type' => 'user',
                    'created_by_user_id' => $user->id,
                    'lead_status_snapshot_id' => $customer->lead_status_id,
                ]);
            }
        }

        // Save broadcast history
        BroadcastEmailHistory::create([
            'user_id' => $user->id,
            'subject' => $request->subject,
            'body' => $request->body,
            'filter_type' => $request->filter_type ?? 'all',
            'area_id' => $request->area_id,
            'recipients' => $allRecipients,
            'recipient_count' => count($allRecipients),
            'has_attachments' => $request->hasFile('attachments'),
        ]);

        return response()->json([
            'message' => 'Broadcast email process completed',
            'sent_count' => $sentCount,
            'failed_count' => count($failedEmails),
            'failed_emails' => $failedEmails,
        ]);
    }

    public function history(Request $request)
    {
        $user = $this->getCurrentUserProfile();
        $query = BroadcastEmailHistory::with('user');

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $history = $query->orderBy('sent_at', 'desc')->paginate(10);
        return response()->json($history);
    }

    public function getDrafts(Request $request)
    {
        $user = $this->getCurrentUserProfile();
        $drafts = BroadcastEmailDraft::where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($drafts);
    }

    public function getDraft($id)
    {
        $user = $this->getCurrentUserProfile();
        $draft = BroadcastEmailDraft::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json($draft);
    }

    public function saveDraft(Request $request)
    {
        $request->validate([
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'filter_type' => 'nullable|in:all,area',
            'area_id' => 'nullable|exists:areas,id',
        ]);

        $user = $this->getCurrentUserProfile();

        $draft = BroadcastEmailDraft::create([
            'user_id' => $user->id,
            'subject' => $request->subject,
            'body' => $request->body,
            'filter_type' => $request->filter_type ?? 'all',
            'area_id' => $request->area_id,
        ]);

        return response()->json([
            'message' => 'Draft saved successfully',
            'draft' => $draft
        ]);
    }

    public function updateDraft(Request $request, $id)
    {
        $request->validate([
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'filter_type' => 'nullable|in:all,area',
            'area_id' => 'nullable|exists:areas,id',
        ]);

        $user = $this->getCurrentUserProfile();
        $draft = BroadcastEmailDraft::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $draft->update([
            'subject' => $request->subject,
            'body' => $request->body,
            'filter_type' => $request->filter_type ?? 'all',
            'area_id' => $request->area_id,
        ]);

        return response()->json([
            'message' => 'Draft updated successfully',
            'draft' => $draft
        ]);
    }

    public function deleteDraft($id)
    {
        $user = $this->getCurrentUserProfile();
        $draft = BroadcastEmailDraft::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $draft->delete();

        return response()->json([
            'message' => 'Draft deleted successfully'
        ]);
    }
}
