<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Request;

class LogAuthentication
{
    public function handleLogin(Login $event): void
    {
        AuditLog::create([
            'tenant_id' => $event->user->tenant_id,
            'user_id' => $event->user->id,
            'action' => 'login',
            'model_type' => get_class($event->user),
            'model_id' => $event->user->id,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        AuditLog::create([
            'tenant_id' => $event->user?->tenant_id,
            'user_id' => $event->user?->id,
            'action' => 'logout',
            'model_type' => $event->user ? get_class($event->user) : null,
            'model_id' => $event->user?->id,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
