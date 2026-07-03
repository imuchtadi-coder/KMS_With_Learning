<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'app' => [
                'name' => config('app.name'),
                'version' => config('app.version'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
            ],
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
        ];

        $healthy = collect($checks)->every(fn ($check) => ($check['status'] ?? 'ok') === 'ok');

        return response()->json([
            'status' => $healthy ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'ok', 'driver' => DB::getDriverName()];
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

    private function checkQueue(): array
    {
        $connection = config('queue.default');
        return ['status' => 'ok', 'connection' => $connection];
    }
}
