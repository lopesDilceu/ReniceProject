<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('gives every response a request id', function () {
    $id = $this->get('/')->headers->get('X-Request-Id');

    expect(Str::isUuid($id))->toBeTrue()
        ->and(Context::get('request_id'))->toBe($id);
});

it('keeps an id set by a proxy, when it looks like one', function () {
    $this->withHeader('X-Request-Id', 'edge-1234abcd')->get('/')->assertHeader('X-Request-Id', 'edge-1234abcd');
});

it('replaces an id that could inject into logs', function (string $incoming) {
    $id = $this->withHeader('X-Request-Id', $incoming)->get('/')->headers->get('X-Request-Id');

    expect($id)->not->toBe($incoming)->and(Str::isUuid($id))->toBeTrue();
})->with([
    'spaces and quotes' => ['abc" injected="1'],
    'too short' => ['abc'],
    'too long' => [str_repeat('a', 65)],
]);

it('is added to the API and to errors too', function () {
    $this->getJson('/api/v1/cart')->assertUnauthorized()->assertHeader('X-Request-Id');
    $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden()->assertHeader('X-Request-Id');
});

it('writes JSON log lines that carry the request id', function () {
    $file = storage_path('framework/testing/json.log');
    @unlink($file);
    config(['logging.channels.json.with.stream' => $file]);

    Context::add('request_id', 'req-12345678');
    Log::channel('json')->warning('Pix webhook rejected', ['provider' => 'mercadopago']);

    $line = json_decode(trim(file_get_contents($file)), true);

    expect($line['message'])->toBe('Pix webhook rejected')
        ->and($line['level_name'])->toBe('WARNING')
        ->and($line['context']['provider'])->toBe('mercadopago')
        ->and($line['extra']['request_id'])->toBe('req-12345678');
});
