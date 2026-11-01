<?php

/*
| Production runs `php artisan config:cache`, which var_export()s every
| config value: an object or closure in a config file breaks the deploy,
| although every test passes (tests never cache config). Found by running
| the production image; see docs/deploy.md.
*/

it('keeps every config value cacheable: no objects or closures, only scalars, arrays and enums', function () {
    $offenders = [];

    $walk = function (mixed $value, string $path) use (&$walk, &$offenders) {
        // Enums are fine: var_export() writes them as Enum::Case.
        if (is_object($value) && ! $value instanceof UnitEnum) {
            $offenders[] = $path.' ('.$value::class.')';
        } elseif (is_array($value)) {
            foreach ($value as $key => $item) {
                $walk($item, "{$path}.{$key}");
            }
        }
    };

    foreach (config()->all() as $key => $value) {
        $walk($value, (string) $key);
    }

    expect($offenders)->toBe([]);
});
