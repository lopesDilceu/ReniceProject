<?php

use App\Models\Address;
use App\Models\Phone;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'Cliente Demo',
        'cpf' => '52998224725',
        'birth_date' => '1995-05-20',
    ]);
});

function profilePayload(array $overrides = []): array
{
    return ['name' => 'Cliente Novo', 'email' => 'novo@example.com', 'birth_date' => '1990-01-01', ...$overrides];
}

it('shows the account page with a masked CPF', function () {
    $this->actingAs($this->user)->get(route('account.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/edit')
            ->where('profile.cpf', '***.982.247-**')
            ->where('profile.birth_date', '1995-05-20'))
        ->assertDontSee('52998224725');
});

describe('SEC-01 privilege escalation', function () {
    it('rejects profile updates from guests', function () {
        $this->patch(route('account.update'), profilePayload())->assertRedirect(route('login'));
    })->issue(17);

    it('no longer has the v1 route that took a user id', function () {
        $victim = User::factory()->create();

        $this->put("/usuarios/edit/{$victim->id}", ['us_adm' => 1, 'is_admin' => 1])->assertNotFound();
        $this->actingAs($this->user)->put("/usuarios/edit/{$victim->id}", ['is_admin' => 1])->assertNotFound();

        expect($victim->fresh()->is_admin)->toBeFalse();
    })->issue(17);

    it('only ever updates the signed-in user', function () {
        $other = User::factory()->create(['name' => 'Outra Pessoa']);

        $this->actingAs($this->user)->patch(route('account.update'), profilePayload(['id' => $other->id]));

        expect($this->user->fresh()->name)->toBe('Cliente Novo')
            ->and($other->fresh()->name)->toBe('Outra Pessoa');
    })->issue(17);

    it('ignores the admin flag and the CPF in the payload', function () {
        $this->actingAs($this->user)->patch(route('account.update'), profilePayload([
            'is_admin' => true,
            'cpf' => '11144477735',
        ]));

        expect($this->user->fresh())
            ->is_admin->toBeFalse()
            ->cpf->toBe('52998224725');
    })->issue(17);
});

it('asks to confirm a changed e-mail', function () {
    Notification::fake();

    $this->actingAs($this->user)->patch(route('account.update'), profilePayload())->assertSessionHasNoErrors();

    expect($this->user->fresh()->hasVerifiedEmail())->toBeFalse();
    Notification::assertSentTo($this->user, VerifyEmail::class);
});

it('keeps the e-mail verified when it does not change', function () {
    $this->actingAs($this->user)->patch(route('account.update'), profilePayload(['email' => $this->user->email]));

    expect($this->user->fresh()->hasVerifiedEmail())->toBeTrue();
});

describe('photo', function () {
    beforeEach(fn () => Storage::fake('public'));

    it('stores the photo and replaces the previous one', function () {
        $this->actingAs($this->user)->post(route('account.avatar.store'), ['avatar' => UploadedFile::fake()->image('a.jpg')]);
        $first = $this->user->fresh()->avatar_path;

        $this->actingAs($this->user)->post(route('account.avatar.store'), ['avatar' => UploadedFile::fake()->image('b.png')]);
        $second = $this->user->fresh()->avatar_path;

        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);
    });

    it('rejects files that are not images or are too big', function (Closure $file) {
        $this->actingAs($this->user)->post(route('account.avatar.store'), ['avatar' => $file()])
            ->assertSessionHasErrors('avatar');

        expect($this->user->fresh()->avatar_path)->toBeNull();
    })->with([
        'pdf' => fn () => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        'svg' => fn () => UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'),
        'over 2 MB' => fn () => UploadedFile::fake()->image('big.jpg')->size(3000),
    ]);

    it('removes the photo', function () {
        $this->actingAs($this->user)->post(route('account.avatar.store'), ['avatar' => UploadedFile::fake()->image('a.jpg')]);
        $path = $this->user->fresh()->avatar_path;

        $this->actingAs($this->user)->delete(route('account.avatar.destroy'));

        expect($this->user->fresh()->avatar_path)->toBeNull();
        Storage::disk('public')->assertMissing($path);
    });
});

it('changes the password only with the current one', function () {
    $this->actingAs($this->user)->put(route('account.password.update'), [
        'current_password' => 'wrong',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertSessionHasErrors('current_password');

    $this->actingAs($this->user)->put(route('account.password.update'), [
        'current_password' => 'password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertSessionHasNoErrors();

    expect(Hash::check('new-password', $this->user->fresh()->password))->toBeTrue();
});

describe('phones and addresses', function () {
    it('adds a phone, stored as digits', function () {
        $this->actingAs($this->user)->post(route('phones.store'), ['number' => '(38) 99999-0000'])->assertSessionHasNoErrors();

        expect($this->user->phones()->sole()->number)->toBe('38999990000');
    });

    it('rejects invalid and duplicated phones', function (string $number) {
        Phone::factory()->for($this->user)->create(['number' => '38999990000']);

        $this->actingAs($this->user)->post(route('phones.store'), ['number' => $number])->assertSessionHasErrors('number');
    })->with(['123', '(38) 99999-0000', '(00) 99999-0000']);

    it('lets customers remove only their own phones and addresses', function () {
        $theirPhone = Phone::factory()->create();
        $theirAddress = Address::factory()->create();
        $myAddress = Address::factory()->for($this->user)->create();

        $this->actingAs($this->user)->delete(route('phones.destroy', $theirPhone))->assertForbidden();
        $this->actingAs($this->user)->delete(route('addresses.destroy', $theirAddress))->assertForbidden();
        $this->actingAs($this->user)->delete(route('addresses.destroy', $myAddress))->assertRedirect();

        expect($theirPhone->fresh())->not->toBeNull()
            ->and($theirAddress->fresh())->not->toBeNull()
            ->and($myAddress->fresh())->toBeNull();
    });
});

it('redirects the v1 account URLs', function (string $old, string $new) {
    $this->get($old)->assertStatus(301)->assertRedirect($new);
})->with([
    ['/usuarios/minha-conta', '/conta'],
    ['/usuarios/editar-dados', '/conta'],
    ['/usuarios/minhas-compras', '/conta/pedidos'],
]);
