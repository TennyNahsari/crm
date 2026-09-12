<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'imap_host',
        'imap_port',
        'imap_username',
        'imap_password',
        'imap_encryption',
        'is_imap_enabled',
        'last_imap_sync_at',
    ];

    protected $casts = [
        'is_imap_enabled' => 'boolean',
        'last_imap_sync_at' => 'datetime',
        'imap_port' => 'integer',
        'mail_port' => 'integer',
    ];

    protected $hidden = [
        'mail_password',
        'imap_password',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
