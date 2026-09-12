<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('email_settings', function (Blueprint $table) {
            $table->string('imap_host')->nullable()->after('mail_from_name');
            $table->integer('imap_port')->nullable()->default(993)->after('imap_host');
            $table->string('imap_username')->nullable()->after('imap_port');
            $table->string('imap_password')->nullable()->after('imap_username');
            $table->string('imap_encryption')->nullable()->default('ssl')->after('imap_password');
            $table->boolean('is_imap_enabled')->default(false)->after('imap_encryption');
            $table->timestamp('last_imap_sync_at')->nullable()->after('is_imap_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_settings', function (Blueprint $table) {
            $table->dropColumn([
                'imap_host',
                'imap_port',
                'imap_username',
                'imap_password',
                'imap_encryption',
                'is_imap_enabled',
                'last_imap_sync_at',
            ]);
        });
    }
};
