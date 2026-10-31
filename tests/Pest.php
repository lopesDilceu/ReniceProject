<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test case
|--------------------------------------------------------------------------
|
| Feature tests boot the Laravel application. Unit and architecture tests
| run without it, so they stay fast.
|
*/

pest()->extend(TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Project
|--------------------------------------------------------------------------
|
| Lets tests reference GitHub issues with ->issue() and ->todo(issue: ...),
| which Pest prints as links.
|
*/

pest()->project()->github('lopesDilceu/ReniceProject');
