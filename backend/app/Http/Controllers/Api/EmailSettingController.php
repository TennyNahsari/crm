<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasTenantUser;
use App\Models\EmailSetting;
use App\Services\ImapSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class EmailSettingController extends Controller
{
    use HasTenantUser;

    public function show(Request $request)
    {
        $userProfile = $this->getCurrentUserProfile();
        $setting = EmailSetting::where('user_id', $userProfile->id)->first();
        
        if ($setting) {
            // Mask passwords for security
            if ($setting->mail_password) {
                $setting->mail_password = '********';
            }
            if ($setting->imap_password) {
                $setting->imap_password = '********';
            }
        }
        
        return response()->json($setting);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string',
            'imap_host' => 'nullable|string',
            'imap_port' => 'nullable|integer',
            'imap_username' => 'nullable|string',
            'imap_password' => 'nullable|string',
            'imap_encryption' => 'nullable|string',
            'is_imap_enabled' => 'nullable|boolean',
        ]);

        if (!empty($validated['mail_from_address'])) {
            $validated['mail_from_address'] = trim(explode(',', $validated['mail_from_address'])[0]);
        }

        $userProfile = $this->getCurrentUserProfile();
        $setting = EmailSetting::where('user_id', $userProfile->id)->first();

        if ($setting) {
            if (isset($validated['mail_password']) && ($validated['mail_password'] === '********' || empty($validated['mail_password']))) {
                unset($validated['mail_password']);
            }
            if (isset($validated['imap_password']) && ($validated['imap_password'] === '********' || empty($validated['imap_password']))) {
                unset($validated['imap_password']);
            }
            $setting->update($validated);
        } else {
            $setting = EmailSetting::create(array_merge($validated, ['user_id' => $userProfile->id]));
        }

        return response()->json([
            'message' => 'Pengaturan email berhasil disimpan',
            'setting' => $setting
        ]);
    }

    public function update(Request $request)
    {
        return $this->store($request);
    }

    public function testSmtp(Request $request)
    {
        $userProfile = $this->getCurrentUserProfile();
        $setting = EmailSetting::where('user_id', $userProfile->id)->first();

        if (!$setting) {
            $setting = new EmailSetting();
            $setting->user_id = $userProfile->id;
        }

        $host = $request->input('mail_host', $setting->mail_host);
        $port = $request->input('mail_port', $setting->mail_port ?: 587);
        $username = $request->input('mail_username', $setting->mail_username);
        $encryption = $request->input('mail_encryption', $setting->mail_encryption ?: 'tls');
        $password = $request->input('mail_password');

        if (!$password || $password === '********') {
            $password = $setting->mail_password;
        }

        if (empty($host) || empty($username) || empty($password)) {
            return response()->json([
                'success' => false,
                'message' => 'Lengkapi Host, Username, dan Password SMTP terlebih dahulu.'
            ], 400);
        }

        try {
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.transport', 'smtp');
            Config::set('mail.mailers.smtp.host', $host);
            Config::set('mail.mailers.smtp.port', $port);
            Config::set('mail.mailers.smtp.username', $username);
            Config::set('mail.mailers.smtp.password', $password);
            Config::set('mail.mailers.smtp.encryption', $encryption);
            Mail::purge('smtp');

            $transport = Mail::mailer('smtp')->getSymfonyTransport();
            if (method_exists($transport, 'start')) {
                $transport->start();
            }

            return response()->json([
                'success' => true,
                'message' => 'Koneksi & Otentikasi SMTP Berhasil! Kredensial valid.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal Otentikasi SMTP: ' . $e->getMessage()
            ], 400);
        }
    }

    public function testImap(Request $request, ImapSyncService $imapService)
    {
        $userProfile = $this->getCurrentUserProfile();
        $setting = EmailSetting::where('user_id', $userProfile->id)->first();

        if (!$setting) {
            $setting = new EmailSetting();
            $setting->user_id = $userProfile->id;
        }

        // Apply request data to temporary setting instance for testing
        if ($request->has('imap_host')) {
            $setting->imap_host = $request->imap_host;
        }
        if ($request->has('imap_port')) {
            $setting->imap_port = $request->imap_port ?: 993;
        }
        if ($request->has('imap_username')) {
            $setting->imap_username = $request->imap_username;
        }
        if ($request->has('imap_encryption')) {
            $setting->imap_encryption = $request->imap_encryption;
        }

        $reqPassword = $request->input('imap_password');
        if ($reqPassword && $reqPassword !== '********') {
            $setting->imap_password = $reqPassword;
        }

        $result = $imapService->testConnection($setting);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 400);
    }

    public function syncImap(Request $request, ImapSyncService $imapService)
    {
        $userProfile = $this->getCurrentUserProfile();
        $setting = EmailSetting::where('user_id', $userProfile->id)->first();

        if (!$setting || !$setting->is_imap_enabled) {
            return response()->json(['message' => 'IMAP belum diaktifkan dalam Pengaturan.'], 400);
        }

        $result = $imapService->sync($setting);

        return response()->json([
            'message' => "Sinkronisasi selesai. {$result['synced']} email berhasil diproses.",
            'synced' => $result['synced'],
            'errors' => $result['errors'],
        ]);
    }
}
