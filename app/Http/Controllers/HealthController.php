<?php

namespace App\Http\Controllers;

use App\Support\Health\HealthCheck;
use Illuminate\Http\JsonResponse;

/**
 * GET /health, for uptime monitors and load balancers: 200 when the app can
 * serve (ok or degraded), 503 when it cannot. Statuses, counts and timings
 * only; failure details go to the logs.
 */
class HealthController extends Controller
{
    public function __invoke(HealthCheck $health): JsonResponse
    {
        $report = $health->run();

        return response()
            ->json($report, $report['status'] === 'fail' ? 503 : 200)
            ->header('Cache-Control', 'no-store');
    }
}
