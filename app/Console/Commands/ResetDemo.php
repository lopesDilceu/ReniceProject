<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

#[Signature('demo:reset')]
#[Description('Rebuild the public demo: fresh database, demo seed, uploaded files removed (DEMO_MODE only)')]
class ResetDemo extends Command
{
    /**
     * Folders on the public disk where visitors can upload files.
     */
    private const UPLOAD_FOLDERS = ['avatars', 'products'];

    public function handle(): int
    {
        // Refuses everywhere else, so it can never wipe a real store.
        if (! Config::boolean('shop.demo')) {
            $this->error('demo:reset only runs with DEMO_MODE=true.');

            return self::FAILURE;
        }

        $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);

        foreach (self::UPLOAD_FOLDERS as $folder) {
            Storage::disk('public')->deleteDirectory($folder);
        }

        $this->info('Demo reset.');

        return self::SUCCESS;
    }
}
