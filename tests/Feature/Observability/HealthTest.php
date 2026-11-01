<?php

use App\Support\Health\HealthCheck;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(fn () => Cache::forever(HealthCheck::SCHEDULER_HEARTBEAT, now()->getTimestamp()));

/**
 * Runs $request with the default connection pointing at a database that
 * does not exist, then restores it: RefreshDatabase rolls back the default
 * connection on teardown and must find the real one.
 */
function withBrokenDatabase(Closure $request): mixed
{
    $default = config('database.default');
    config(['database.connections.broken' => ['driver' => 'sqlite', 'database' => '/nonexistent/renice.sqlite']]);
    config(['database.default' => 'broken']);

    try {
        return $request();
    } finally {
        config(['database.default' => $default]);
        DB::purge('broken');
    }
}

it('reports every check ok', function () {
    $this->getJson('/health')
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonPath('status', 'ok')
        ->assertJsonPath('checks.database.status', 'ok')
        ->assertJsonPath('checks.cache.status', 'ok')
        ->assertJsonPath('checks.queue.status', 'ok')
        ->assertJsonPath('checks.scheduler.status', 'ok')
        ->assertJsonStructure(['checks' => ['database' => ['latency_ms'], 'cache' => ['latency_ms']]]);
});

it('is degraded, not down, when jobs wait too long or the scheduler stops', function () {
    config(['queue.default' => 'database']);
    DB::table('jobs')->insert(['queue' => 'default', 'payload' => '{}', 'attempts' => 0, 'available_at' => now()->subMinutes(10)->getTimestamp(), 'created_at' => now()->subMinutes(10)->getTimestamp()]);
    DB::table('failed_jobs')->insert(['uuid' => (string) Str::uuid(), 'connection' => 'database', 'queue' => 'default', 'payload' => '{}', 'exception' => 'x']);
    Cache::forever(HealthCheck::SCHEDULER_HEARTBEAT, now()->subMinutes(10)->getTimestamp());

    $this->getJson('/health')
        ->assertOk()
        ->assertJsonPath('status', 'degraded')
        ->assertJsonPath('checks.queue.status', 'degraded')
        ->assertJsonPath('checks.queue.pending', 1)
        ->assertJsonPath('checks.queue.failed', 1)
        ->assertJsonPath('checks.scheduler.status', 'degraded');
});

it('answers 503 without revealing why when the database is down', function () {
    Log::spy();

    withBrokenDatabase(fn () => $this->getJson('/health'))
        ->assertStatus(503)
        ->assertJsonPath('status', 'fail')
        ->assertJsonPath('checks.database.status', 'fail')
        ->assertDontSee('nonexistent');

    Log::shouldHaveReceived('error')->withArgs(fn (string $message) => str_contains($message, '"database"'));
});

it('answers 503 when the cache is down', function () {
    Cache::shouldReceive('put')->andThrow(new RuntimeException('cache down'));
    Cache::shouldReceive('get')->andReturn(null);

    $this->getJson('/health')->assertStatus(503)->assertJsonPath('checks.cache.status', 'fail');
});

it('makes Laravel\'s /up fail when the database is down', function () {
    $this->get('/up')->assertOk();

    withBrokenDatabase(fn () => $this->get('/up'))->assertServerError();
});

it('starts no session for monitors', function () {
    expect(collect($this->get('/health')->headers->getCookies())->map->getName()->all())
        ->not->toContain(config('session.cookie'));
});

it('records a scheduler heartbeat every minute', function () {
    $event = collect(app(Schedule::class)->events())->first(fn (Event $event) => $event->description === 'health:scheduler-heartbeat');

    expect($event)->not->toBeNull()->and($event->expression)->toBe('* * * * *');
});
