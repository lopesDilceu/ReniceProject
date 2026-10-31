<?php

arch('no debugging calls are left in the code')
    ->preset()
    ->php();

arch('no insecure functions are used')
    ->preset()
    ->security();

arch('controllers are suffixed')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller');

arch('models extend Eloquent')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model');

arch('enums are backed enums')
    ->expect('App\Enums')
    ->toBeStringBackedEnums();
