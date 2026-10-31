<?php

/*
| The Larastan baseline existed only for v1 code waiting to be rewritten.
| With every v1 file gone (#31), static analysis ignores nothing: no
| baseline file, no included baseline and no ignoreErrors entries.
*/

it('ignores no static analysis errors', function () {
    $config = (string) file_get_contents(__DIR__.'/../../phpstan.neon');

    expect(file_exists(__DIR__.'/../../phpstan-baseline.neon'))->toBeFalse()
        ->and($config)->not->toContain('phpstan-baseline')
        ->and($config)->not->toContain('ignoreErrors');
});
