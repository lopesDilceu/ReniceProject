<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('opens the Pulse dashboard to admins only', function () {
    $this->get('/pulse')->assertForbidden();
    $this->actingAs(User::factory()->create())->get('/pulse')->assertForbidden();

    $this->actingAs(User::factory()->admin()->create())->get('/pulse')->assertOk();
});
