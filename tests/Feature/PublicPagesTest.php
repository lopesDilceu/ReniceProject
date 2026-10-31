<?php

/*
| Smoke tests for the pages that render without the database. Pages that
| query domain tables are covered once the schema exists (issue #11).
*/

it('renders public pages', function (string $uri, string $title) {
    $this->get($uri)
        ->assertOk()
        ->assertSee("Renice - {$title}", escape: false);
})->with([
    'home' => ['/', 'Home'],
    'about' => ['/sobre', 'Sobre'],
    'login' => ['/usuarios/login', 'Login'],
    'sign-up' => ['/usuarios/cadastro', 'Cadastro'],
]);

it('exposes a health check', function () {
    $this->get('/up')->assertOk();
});

it('sends guests away from the admin area', function () {
    $this->get('/adm')->assertRedirect('/');
});
