<?php

namespace App\Services;

class TenantService
{
    public function setTenantByCompany($company)
    {
        return $this;
    }
    
    public function setTenantBySession()
    {
        return $this;
    }
    
    public function getCurrentTenant()
    {
        return null;
    }
    
    public function getTenantDatabase()
    {
        return config('database.connections.pgsql.database');
    }
}
