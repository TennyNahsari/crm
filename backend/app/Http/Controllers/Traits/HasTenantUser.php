<?php

namespace App\Http\Controllers\Traits;

use App\Models\User;

trait HasTenantUser
{
    /**
     * Get current authenticated user
     * 
     * @return \App\Models\User
     */
    protected function getCurrentUserProfile()
    {
        $user = auth()->user();
        
        if (!$user) {
            abort(401, 'Unauthenticated');
        }
        
        return $user;
    }
}
