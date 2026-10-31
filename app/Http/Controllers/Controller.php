<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

abstract class Controller
{
    /**
     * Shows a toast on the next Inertia page (see use-flash-toast.ts).
     *
     * @param  'success'|'info'|'warning'|'error'  $type
     */
    protected function toast(string $type, string $message): void
    {
        Inertia::flash('toast', ['type' => $type, 'message' => $message]);
    }
}
