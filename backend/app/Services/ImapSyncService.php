<?php

namespace App\Services;

use App\Models\EmailSetting;
use App\Models\Customer;
use App\Models\Contact;
use App\Models\Interaction;
use App\Models\Email;
use Illuminate\Support\Facades\Log;

class ImapSyncService
{
    /**
     * Test IMAP Connection
     *
     * @param EmailSetting $setting
     * @return array ['success' => bool, 'message' => string]
     */
    public function testConnection(EmailSetting $setting): array
    {
        if (empty($setting->imap_host) || empty($setting->imap_username) || empty($setting->imap_password)) {
            return [
                'success' => false,
                'message' => 'Lengkapi Host IMAP, Username, dan Password terlebih dahulu.'
            ];
        }

        $host = $setting->imap_host;
        $port = $setting->imap_port ?: 993;
        $encryption = strtolower($setting->imap_encryption ?: 'ssl');
        $username = $setting->imap_username;
        $password = $setting->imap_password;

        if (function_exists('imap_open')) {
            $flags = "/imap";
            if ($encryption === 'ssl') {
                $flags .= "/ssl/novalidate-cert";
            } elseif ($encryption === 'tls') {
                $flags .= "/tls/novalidate-cert";
            } else {
                $flags .= "/notls";
            }

            $mailbox = "{" . $host . ":" . $port . $flags . "}INBOX";
            $mbox = @imap_open($mailbox, $username, $password, OP_HALFOPEN, 1);
            if ($mbox) {
                @imap_close($mbox);
                return [
                    'success' => true,
                    'message' => 'Koneksi IMAP berhasil terhubung!'
                ];
            }
        }

        // Socket IMAP Connection & Login check
        $socket = $this->connectSocket($host, $port, $encryption);
        if (!$socket) {
            return [
                'success' => false,
                'message' => "Gagal terhubung ke IMAP Server $host:$port via Socket."
            ];
        }

        $loginOk = $this->loginSocket($socket, $username, $password);
        $this->closeSocket($socket);

        if ($loginOk) {
            return [
                'success' => true,
                'message' => 'Koneksi & Otentikasi IMAP berhasil terhubung (Native Socket OK)!'
            ];
        }

        return [
            'success' => false,
            'message' => 'Gagal Otentikasi IMAP: Username atau Password IMAP ditolak oleh server.'
        ];
    }

    /**
     * Synchronize emails from IMAP inbox
     *
     * @param EmailSetting $setting
     * @return array ['synced' => int, 'errors' => array]
     */
    public function sync(EmailSetting $setting): array
    {
        if (!$setting->is_imap_enabled || empty($setting->imap_host)) {
            return ['synced' => 0, 'errors' => ['IMAP sync disnonaktifkan']];
        }

        $host = $setting->imap_host;
        $port = $setting->imap_port ?: 993;
        $encryption = strtolower($setting->imap_encryption ?: 'ssl');
        $username = $setting->imap_username;
        $password = $setting->imap_password;

        if (function_exists('imap_open')) {
            return $this->syncWithPhpImap($setting, $host, $port, $encryption, $username, $password);
        }

        return $this->syncWithSocket($setting, $host, $port, $encryption, $username, $password);
    }

    /**
     * Native Stream Socket IMAP Synchronizer
     */
    private function syncWithSocket($setting, $host, $port, $encryption, $username, $password): array
    {
        $syncedCount = 0;
        $errors = [];

        $socket = $this->connectSocket($host, $port, $encryption);
        if (!$socket) {
            return ['synced' => 0, 'errors' => ["Gagal koneksi socket ke $host:$port"]];
        }

        if (!$this->loginSocket($socket, $username, $password)) {
            $this->closeSocket($socket);
            return ['synced' => 0, 'errors' => ['Otentikasi login IMAP socket ditolak']];
        }

        // Select INBOX
        $this->sendCmd($socket, 'A002', 'SELECT INBOX');
        $response = $this->readResponse($socket, 'A002');

        // Search for UNSEEN or recent emails
        $this->sendCmd($socket, 'A003', 'SEARCH UNSEEN');
        $searchResp = $this->readResponse($socket, 'A003');
        $emailIds = $this->parseSearchResponse($searchResp);

        if (empty($emailIds)) {
            // Fallback search ALL (latest 30)
            $this->sendCmd($socket, 'A004', 'SEARCH ALL');
            $searchRespAll = $this->readResponse($socket, 'A004');
            $emailIds = $this->parseSearchResponse($searchRespAll);
            if (!empty($emailIds)) {
                rsort($emailIds);
                $emailIds = array_slice($emailIds, 0, 30);
            }
        }

        foreach ($emailIds as $idx => $emailId) {
            try {
                $tag = 'FETCH' . ($idx + 10);
                $this->sendCmd($socket, $tag, "FETCH $emailId (BODY[HEADER] BODY[TEXT])");
                $fetchResp = $this->readResponse($socket, $tag);

                $headers = $this->parseHeaders($fetchResp);
                $fromEmail = $headers['from_email'] ?? '';
                $fromName = $headers['from_name'] ?? $fromEmail;
                $subject = $headers['subject'] ?? '(No Subject)';
                $messageId = $headers['message_id'] ?? ('socket_msg_' . $emailId . '_' . md5($subject));
                $dateStr = $headers['date'] ?? now();
                $body = $this->parseBody($fetchResp);

                if (empty($fromEmail)) {
                    continue;
                }

                // Skip if already processed in database
                if (Email::where('message_id', $messageId)->exists()) {
                    continue;
                }

                // Match customer by email
                $customer = Customer::whereRaw('LOWER(email) = ?', [strtolower($fromEmail)])->first();
                if (!$customer) {
                    $contact = Contact::whereRaw('LOWER(email) = ?', [strtolower($fromEmail)])->first();
                    if ($contact) {
                        $customer = $contact->customer;
                    }
                }

                // Save Email record
                Email::create([
                    'customer_id' => $customer ? $customer->id : null,
                    'message_id' => $messageId,
                    'from_email' => $fromEmail,
                    'from_name' => $fromName,
                    'to_emails' => [$username],
                    'subject' => $subject,
                    'body_html' => $body,
                    'body_text' => strip_tags($body),
                    'is_inbound' => true,
                    'is_processed' => true,
                    'email_date' => date('Y-m-d H:i:s', strtotime($dateStr)),
                ]);

                // Create interaction record on Customer timeline
                if ($customer) {
                    Interaction::create([
                        'customer_id' => $customer->id,
                        'interaction_type' => 'email_inbound',
                        'channel' => 'email',
                        'subject' => $subject,
                        'content' => "Pesan dari: $fromName ($fromEmail)\n\n" . strip_tags($body),
                        'summary' => 'Email masuk: ' . $subject,
                        'interaction_at' => date('Y-m-d H:i:s', strtotime($dateStr)),
                        'created_by_type' => 'system',
                        'created_by_user_id' => $setting->user_id,
                        'lead_status_snapshot_id' => $customer->lead_status_id,
                    ]);
                }

                $syncedCount++;
            } catch (\Exception $e) {
                Log::error('Error in socket IMAP email fetch: ' . $e->getMessage());
                $errors[] = $e->getMessage();
            }
        }

        $this->sendCmd($socket, 'A999', 'LOGOUT');
        $this->closeSocket($socket);

        $setting->update(['last_imap_sync_at' => now()]);

        return [
            'synced' => $syncedCount,
            'errors' => $errors
        ];
    }

    private function connectSocket($host, $port, $encryption)
    {
        $transport = ($encryption === 'ssl') ? 'ssl://' : (($encryption === 'tls') ? 'tls://' : '');
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $fp = @stream_socket_client($transport . $host . ':' . $port, $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
        if (!$fp) {
            return false;
        }

        stream_set_timeout($fp, 10);
        fgets($fp, 1024); // read greeting
        return $fp;
    }

    private function sendCmd($socket, $tag, $cmd)
    {
        fwrite($socket, "$tag $cmd\r\n");
    }

    private function readResponse($socket, $tag): string
    {
        $buffer = '';
        while (!feof($socket)) {
            $line = fgets($socket, 4096);
            if ($line === false) break;
            $buffer .= $line;
            if (str_starts_with($line, $tag . ' ')) {
                break;
            }
        }
        return $buffer;
    }

    private function loginSocket($socket, $username, $password): bool
    {
        // Escape quotes in password
        $escapedPass = str_replace(['\\', '"'], ['\\\\', '\"'], $password);
        $this->sendCmd($socket, 'A001', "LOGIN \"$username\" \"$escapedPass\"");
        $resp = $this->readResponse($socket, 'A001');
        return str_contains($resp, 'A001 OK');
    }

    private function closeSocket($socket)
    {
        if ($socket) {
            @fclose($socket);
        }
    }

    private function parseSearchResponse(string $resp): array
    {
        $ids = [];
        $lines = explode("\r\n", $resp);
        foreach ($lines as $line) {
            if (str_starts_with($line, '* SEARCH')) {
                $parts = explode(' ', trim($line));
                array_shift($parts); // remove '*'
                array_shift($parts); // remove 'SEARCH'
                foreach ($parts as $p) {
                    if (is_numeric($p)) {
                        $ids[] = (int)$p;
                    }
                }
            }
        }
        return $ids;
    }

    private function parseHeaders(string $raw): array
    {
        $headers = [
            'from_email' => '',
            'from_name' => '',
            'subject' => '(No Subject)',
            'message_id' => '',
            'date' => now(),
        ];

        if (preg_match('/From:\s*(.*?)\r?\n/i', $raw, $m)) {
            $fromStr = trim($m[1]);
            if (preg_match('/<([^>]+)>/', $fromStr, $mEmail)) {
                $headers['from_email'] = trim($mEmail[1]);
                $namePart = trim(str_replace($mEmail[0], '', $fromStr), ' "\'');
                $headers['from_name'] = $namePart ?: $headers['from_email'];
            } else {
                $headers['from_email'] = trim($fromStr, ' "\'');
                $headers['from_name'] = $headers['from_email'];
            }
        }

        if (preg_match('/Subject:\s*(.*?)\r?\n/i', $raw, $m)) {
            $headers['subject'] = trim($m[1]);
        }

        if (preg_match('/Message-ID:\s*(.*?)\r?\n/i', $raw, $m)) {
            $headers['message_id'] = trim($m[1], ' <>');
        }

        if (preg_match('/Date:\s*(.*?)\r?\n/i', $raw, $m)) {
            $headers['date'] = trim($m[1]);
        }

        return $headers;
    }

    private function parseBody(string $raw): string
    {
        $parts = explode("\r\n\r\n", $raw, 2);
        return isset($parts[1]) ? trim($parts[1]) : '';
    }

    private function syncWithPhpImap($setting, $host, $port, $encryption, $username, $password): array
    {
        $syncedCount = 0;
        $errors = [];

        $flags = "/imap";
        if ($encryption === 'ssl') {
            $flags .= "/ssl/novalidate-cert";
        } elseif ($encryption === 'tls') {
            $flags .= "/tls/novalidate-cert";
        } else {
            $flags .= "/notls";
        }

        $mailbox = "{" . $host . ":" . $port . $flags . "}INBOX";
        $mbox = @imap_open($mailbox, $username, $password);

        if (!$mbox) {
            $errors[] = 'Gagal buka kotak surat IMAP: ' . imap_last_error();
            return ['synced' => 0, 'errors' => $errors];
        }

        $emails = imap_search($mbox, 'UNSEEN');
        if (!$emails) {
            $emails = imap_search($mbox, 'ALL');
        }

        if ($emails) {
            rsort($emails);
            $emailsToProcess = array_slice($emails, 0, 30);

            foreach ($emailsToProcess as $emailNumber) {
                try {
                    $overview = imap_fetch_overview($mbox, $emailNumber, 0);
                    $header = imap_headerinfo($mbox, $emailNumber);

                    $fromEmail = isset($header->from[0]->mailbox) && isset($header->from[0]->host) 
                        ? strtolower($header->from[0]->mailbox . '@' . $header->from[0]->host) 
                        : '';

                    $fromName = isset($header->from[0]->personal) 
                        ? imap_utf8($header->from[0]->personal) 
                        : $fromEmail;

                    $subject = isset($overview[0]->subject) ? imap_utf8($overview[0]->subject) : '(No Subject)';
                    $dateStr = isset($overview[0]->date) ? $overview[0]->date : now();
                    $messageId = isset($header->message_id) ? $header->message_id : uniqid('msg_');

                    if (Email::where('message_id', $messageId)->exists()) {
                        continue;
                    }

                    $body = imap_fetchbody($mbox, $emailNumber, 1.2);
                    if (!$body) {
                        $body = imap_fetchbody($mbox, $emailNumber, 1);
                    }

                    $customer = Customer::whereRaw('LOWER(email) = ?', [$fromEmail])->first();
                    if (!$customer) {
                        $contact = Contact::whereRaw('LOWER(email) = ?', [$fromEmail])->first();
                        if ($contact) {
                            $customer = $contact->customer;
                        }
                    }

                    Email::create([
                        'customer_id' => $customer ? $customer->id : null,
                        'message_id' => $messageId,
                        'from_email' => $fromEmail,
                        'from_name' => $fromName,
                        'to_emails' => [$username],
                        'subject' => $subject,
                        'body_html' => $body,
                        'body_text' => strip_tags($body),
                        'is_inbound' => true,
                        'is_processed' => true,
                        'email_date' => date('Y-m-d H:i:s', strtotime($dateStr)),
                    ]);

                    if ($customer) {
                        Interaction::create([
                            'customer_id' => $customer->id,
                            'interaction_type' => 'email_inbound',
                            'channel' => 'email',
                            'subject' => $subject,
                            'content' => "Pesan dari: $fromName ($fromEmail)\n\n" . strip_tags($body),
                            'summary' => 'Email masuk: ' . $subject,
                            'interaction_at' => date('Y-m-d H:i:s', strtotime($dateStr)),
                            'created_by_type' => 'system',
                            'created_by_user_id' => $setting->user_id,
                            'lead_status_snapshot_id' => $customer->lead_status_id,
                        ]);
                    }

                    $syncedCount++;
                } catch (\Exception $e) {
                    Log::error('Error processing IMAP email: ' . $e->getMessage());
                    $errors[] = $e->getMessage();
                }
            }
        }

        @imap_close($mbox);
        $setting->update(['last_imap_sync_at' => now()]);

        return [
            'synced' => $syncedCount,
            'errors' => $errors
        ];
    }
}
