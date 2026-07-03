<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AppHealthCommand extends Command
{
    protected $signature = 'app:health {--json : Output as JSON}';
    protected $description = 'Check application health status';

    public function handle(): int
    {
        $checks = [];

        $checks['app'] = [
            'name' => config('app.name'),
            'version' => config('app.version'),
            'env' => config('app.env'),
        ];

        $checks['database'] = $this->checkDatabase();
        $checks['cache'] = $this->checkCache();

        $allOk = collect($checks)->every(fn ($c) => ($c['status'] ?? 'ok') === 'ok');

        if ($this->option('json')) {
            $this->line(json_encode([
                'status' => $allOk ? 'healthy' : 'degraded',
                'timestamp' => now()->toIso8601String(),
                'checks' => $checks,
            ], JSON_PRETTY_PRINT));
            return $allOk ? Command::SUCCESS : Command::FAILURE;
        }

        $this->components->twoColumnDetail('Component', 'Status');
        foreach ($checks as $name => $result) {
            $status = ($result['status'] ?? 'ok') === 'ok';
            $this->components->twoColumnDetail(
                ucfirst($name),
                $status ? '<fg=green>OK</>' : '<fg=red>FAIL</>'
            );
            foreach ($result as $key => $value) {
                if ($key !== 'status') {
                    $this->line("  {$key}: {$value}");
                }
            }
        }

        if ($allOk) {
            $this->components->success('Application is healthy.');
        } else {
            $this->components->error('Application has degraded components.');
        }

        return $allOk ? Command::SUCCESS : Command::FAILURE;
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'ok', 'driver' => DB::getDriverName(), 'database' => DB::connection()->getDatabaseName()];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = '__health_' . now()->timestamp;
            Cache::store(config('cache.default'))->put($key, true, 1);
            $value = Cache::store(config('cache.default'))->get($key);
            Cache::store(config('cache.default'))->forget($key);
            return ['status' => $value ? 'ok' : 'error', 'driver' => config('cache.default')];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
