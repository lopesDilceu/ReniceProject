<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('q'));

        $users = User::query()
            ->withCount('orders')
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->whereLike('name', "%{$search}%")
                ->orWhereLike('email', "%{$search}%")))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/customers/index', [
            'customers' => $users->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified' => $user->hasVerifiedEmail(),
                'is_admin' => $user->is_admin,
                'orders_count' => $user->orders_count,
                'created_at' => $user->created_at?->toIso8601String(),
            ]),
            'filters' => ['q' => $search],
        ]);
    }

    /**
     * Granting or removing admin access is its own action, never part of a
     * profile update (SEC-01). Admins cannot remove their own access, so the
     * store always keeps at least the admin who made the change.
     */
    public function toggleAdmin(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            $this->toast('error', 'Você não pode alterar o seu próprio acesso.');

            return back();
        }

        $user->forceFill(['is_admin' => ! $user->is_admin])->save();

        $this->toast('success', $user->is_admin
            ? "{$user->name} agora é administrador."
            : "{$user->name} não é mais administrador.");

        return back();
    }
}
