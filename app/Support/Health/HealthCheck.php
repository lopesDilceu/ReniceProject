<?php

namespace App\Support\Health;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * What /health reports. "fail" means the app cannot serve requests (database
 * or cache down) and answers 503; "degraded" means it serves them but
 * background work is late (queue backlog, scheduler not running).
 */
final class HealthCheck
{
    public const SCHEDULER_HEARTBEAT = 'health:scheduler-heartbeat';

    /** A job waiting longer than this means no worker is picking jobs up. */
    private const QUEUE_LAG_SECONDS = 300;

    /** The heartbeat runs every minute; allow one missed run. */
    private const SCHEDULER_STALE_SECONDS = 150;

    /**
     * @return array{status: string, checks: array<string, array<string, mixed>>}
     */
    public function run(): array
    {
        $checks = [
            'database' => $this->probe('database', fn () => DB::select('select 1')),
            'cache' => $this->probe('cache', function () {
                $nonce = Str::random(16);
                Cache::put('health:probe', $nonce, 10);

                if (Cache::get('health:probe') !== $nonce) {
                    throw new \RuntimeException('Cache did not return the value just written.');
                }
            }),
            'queue' => $this->queue(),
            'scheduler' => $this->scheduler(),
        ];

        $statuses = array_column($checks, 'status');

        return [
            'status' => in_array('fail', $statuses, true) ? 'fail' : (in_array('degraded', $statuses, true) ? 'degraded' : 'ok'),
            'checks' => $checks,
        ];
    }

    /**
     * @return array{status: string, latency_ms: float}
     */
    private function probe(string $name, callable $check): array
    {
        $start = hrtime(true);

        try {
            $check();
            $status = 'ok';
        } catch (Throwable $exception) {
            // The reason goes to the logs, never to the public response.
            Log::error("Health check \"{$name}\" failed.", ['exception' => $exception->getMessage()]);
            $status = 'fail';
        }

        return ['status' => $status, 'latency_ms' => round((hrtime(true) - $start) / 1e6, 2)];
    }

    /**
     * @return array<string, mixed>
     */
    private function queue(): array
    {
        if (Config::get('queue.default') !== 'database') {
            return ['status' => 'ok', 'driver' => Config::get('queue.default')];
        }

        try {
            $jobs = DB::table((string) Config::get('queue.connections.database.table', 'jobs'));
            $pending = (clone $jobs)->whereNull('reserved_at')->where('available_at', '<=', now()->getTimestamp())->count();
            $oldest = (clone $jobs)->whereNull('reserved_at')->where('available_at', '<=', now()->getTimestamp())->min('available_at');
            $lag = $oldest === null ? 0 : now()->getTimestamp() - (int) $oldest;
            $failed = DB::table((string) Config::get('queue.failed.table', 'failed_jobs'))->count();
        } catch (Throwable $exception) {
            Log::error('Health check "queue" failed.', ['exception' => $exception->getMessage()]);

            return ['status' => 'fail'];
        }

        return [
            'status' => $lag > self::QUEUE_LAG_SECONDS ? 'degraded' : 'ok',
            'pending' => $pending,
            'oldest_pending_seconds' => $lag,
            'failed' => $failed,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function scheduler(): array
    {
        $beat = Cache::get(self::SCHEDULER_HEARTBEAT);

        if (! is_int($beat)) {
            return ['status' => 'degraded', 'last_run_seconds_ago' => null];
        }

        $ago = now()->getTimestamp() - $beat;

        return ['status' => $ago > self::SCHEDULER_STALE_SECONDS ? 'degraded' : 'ok', 'last_run_seconds_ago' => $ago];
    }
}
