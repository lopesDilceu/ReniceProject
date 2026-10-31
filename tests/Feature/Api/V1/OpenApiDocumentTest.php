<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

it('matches the code: the committed document is regenerated, not hand-edited', function () {
    // Scramble reads column types from the database, so the reference
    // document is generated against PostgreSQL (the production database).
    if (DB::getDriverName() !== 'pgsql') {
        $this->markTestSkipped('The OpenAPI document is generated against PostgreSQL.');
    }

    $fresh = storage_path('framework/testing/openapi.json');
    File::ensureDirectoryExists(dirname($fresh));

    $this->artisan('scramble:export', ['--path' => $fresh])->assertSuccessful();

    expect(json_decode(File::get($fresh), true))->toEqual(
        json_decode(File::get(base_path('docs/api/openapi.json')), true),
        'docs/api/openapi.json is out of date: run `php artisan scramble:export` against PostgreSQL and commit it.',
    );
})->group('pgsql');

it('publishes the documentation without signing in', function () {
    $this->getJson('/docs/api.json')
        ->assertOk()
        ->assertJsonPath('info.title', 'Renice')
        ->assertJsonPath('info.version', '1.0.0');

    $this->get('/docs/api')->assertOk();
});
