<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Update foreign keys from 'users' table to 'user_profiles' table
     * This is for multi-tenant architecture where user profiles are in tenant DB
     */
    public function up(): void
    {
        // No-op for single database architecture (all foreign keys reference 'users' table directly)
    }

    public function down(): void
    {
        // No-op
    }
};
