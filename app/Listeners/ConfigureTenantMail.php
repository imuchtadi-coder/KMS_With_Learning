<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Config;
use Stancl\Tenancy\Events\TenancyInitialized;

class ConfigureTenantMail
{
    public function handle(TenancyInitialized $event): void
    {
        $tenant = $event->tenancy->tenant;

        if ($tenant->mail_host && $tenant->mail_username) {
            Config::set('mail.mailers.smtp.host', $tenant->mail_host);
            Config::set('mail.mailers.smtp.port', $tenant->mail_port ?? 587);
            Config::set('mail.mailers.smtp.username', $tenant->mail_username);
            Config::set('mail.mailers.smtp.encryption', $tenant->mail_encryption ?? 'tls');

            if ($tenant->mail_password_encrypted) {
                Config::set('mail.mailers.smtp.password', Crypt::decryptString($tenant->mail_password_encrypted));
            }
        }

        if ($tenant->mail_from_address) {
            Config::set('mail.from.address', $tenant->mail_from_address);
            Config::set('mail.from.name', $tenant->mail_from_name ?? config('mail.from.name'));
        }
    }
}
