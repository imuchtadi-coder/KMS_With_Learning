<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class CleanAuditLogs extends Command
{
    protected $signature = 'audit-log:clean {--days=90 : Delete logs older than this many days}';
    protected $description = 'Delete audit logs older than the specified retention period';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $deleted = AuditLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted {$deleted} audit log(s) older than {$days} days.");

        return Command::SUCCESS;
    }
}
