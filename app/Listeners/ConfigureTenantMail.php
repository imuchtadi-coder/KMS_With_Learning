<?php

namespace App\Listeners;

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Stancl\Tenancy\Events\TenancyInitialized;

class ConfigureTenantMail
{
    public function handle(TenancyInitialized $event): void
    {
        $this->configure($event->tenancy->tenant);
    }

    public function configure(Tenant $tenant): void
    {
        $this->reset();

        $host = trim((string) $tenant->mail_host);

        if ($host === '') {
            return;
        }

        $encryption = strtolower((string) ($tenant->mail_encryption ?: 'tls'));
        $port = (int) ($tenant->mail_port ?: ($encryption === 'ssl' ? 465 : 587));

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.url', null);
        Config::set('mail.mailers.smtp.host', $host);
        Config::set('mail.mailers.smtp.port', $port);
        Config::set('mail.mailers.smtp.username', $tenant->mail_username ?: null);
        Config::set('mail.mailers.smtp.scheme', $encryption === 'ssl' ? 'smtps' : 'smtp');

        $password = $tenant->mail_password_encrypted
            ? Crypt::decryptString($tenant->mail_password_encrypted)
            : null;

        Config::set('mail.mailers.smtp.password', $password);

        if ($tenant->mail_from_address) {
            Config::set('mail.from.address', $tenant->mail_from_address);
            Config::set('mail.from.name', $tenant->mail_from_name ?: config('mail.tenant_fallback.from.name'));
        }

        Mail::purge('smtp');
    }

    public function reset(): void
    {
        $fallback = config('mail.tenant_fallback');

        Config::set('mail.default', $fallback['default']);
        Config::set('mail.from', $fallback['from']);

        foreach ($fallback['smtp'] as $key => $value) {
            Config::set("mail.mailers.smtp.{$key}", $value);
        }

        Mail::purge('smtp');
    }
}
