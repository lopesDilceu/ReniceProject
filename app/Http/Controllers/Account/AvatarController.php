<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateAvatarRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * The file is stored only after validation and the previous one is deleted.
 * v1 stored the upload before validating, never saved its path, and crashed
 * when no file was sent (BUG-05).
 */
class AvatarController extends Controller
{
    public function store(UpdateAvatarRequest $request): RedirectResponse
    {
        $user = $request->user();
        $previous = $user->avatar_path;

        $user->update(['avatar_path' => $request->file('avatar')->store('avatars', 'public')]);

        if ($previous) {
            Storage::disk('public')->delete($previous);
        }

        $this->toast('success', 'Foto atualizada.');

        return back();
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        return back();
    }
}
