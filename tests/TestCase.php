<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Feature tests assert on responses, not on built assets: the PHP CI
        // jobs run without `npm run build`.
        $this->withoutVite();
    }
}
