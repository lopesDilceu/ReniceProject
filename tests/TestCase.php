<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Feature tests assert on responses, not on built assets: the PHP CI
        // jobs run without `npm run build`.
        $this->withoutVite();

        // No test talks to a real payment provider: unfaked requests fail.
        Http::preventStrayRequests();
    }
}
