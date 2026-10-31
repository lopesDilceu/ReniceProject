<?php

use App\Http\Resources\ReviewResource;

it('shows reviewers as first name and last initial', function (string $name, string $expected) {
    expect(ReviewResource::displayName($name))->toBe($expected);
})->with([
    ['Maria Clara Souza', 'Maria S.'],
    ['João silva', 'João S.'],
    ['Ana', 'Ana'],
    ['  Pedro   Álvares  ', 'Pedro Á.'],
]);
