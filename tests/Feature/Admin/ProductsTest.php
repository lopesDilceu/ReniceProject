<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->admin()->create();
});

it('creates a product with price, image and a unique slug, out of stock', function () {
    Product::factory()->create(['name' => 'Antigo', 'slug' => 'copo-gelo']);

    $this->actingAs($this->admin)->post(route('admin.products.store'), [
        'name' => 'Copo Gelo',
        'description' => 'Copo de 300ml.',
        'price' => '11,25',
        'image' => UploadedFile::fake()->image('copo.jpg'),
    ])->assertRedirect(route('admin.products.index'));

    $product = Product::firstWhere('name', 'Copo Gelo');

    expect($product)
        ->slug->toBe('copo-gelo-2')
        ->price_cents->toBe(1125)
        ->stock->toBe(0);
    Storage::disk('public')->assertExists($product->image_path);
});

it('requires a valid price (v1 never saved it)', function (?string $price) {
    $this->actingAs($this->admin)->post(route('admin.products.store'), ['name' => 'Sem preço', 'price' => $price])
        ->assertSessionHasErrors();

    expect(Product::count())->toBe(0);
})->with([null, '', 'grátis', '0', '-5,00'])->issue(25);

it('accepts prices with thousands separators', function () {
    $this->actingAs($this->admin)->post(route('admin.products.store'), ['name' => 'Kit festa', 'price' => 'R$ 1.250,90']);

    expect(Product::sole()->price_cents)->toBe(125090);
});

it('updates a product, keeps its slug and replaces the image', function () {
    $this->actingAs($this->admin)->post(route('admin.products.store'), [
        'name' => 'Copo', 'price' => '10,00', 'image' => UploadedFile::fake()->image('a.jpg'),
    ]);
    $product = Product::sole();
    $oldImage = $product->image_path;

    $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
        'name' => 'Copo renomeado', 'price' => '12,50', 'image' => UploadedFile::fake()->image('b.png'),
    ])->assertSessionHasNoErrors();

    expect($product->fresh())
        ->name->toBe('Copo renomeado')
        ->slug->toBe('copo')
        ->price_cents->toBe(1250);
    Storage::disk('public')->assertMissing($oldImage);
});

it('removes a product from the catalog and restores it', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->admin)->delete(route('admin.products.destroy', $product));
    expect($product->fresh()->trashed())->toBeTrue();

    $this->actingAs($this->admin)->get(route('admin.products.index', ['removed' => 1]))
        ->assertInertia(fn (Assert $page) => $page->has('products.data', 1)->where('products.data.0.deleted', true));

    $this->actingAs($this->admin)->patch(route('admin.products.restore', $product));
    expect($product->fresh()->trashed())->toBeFalse();
});

it('shows exact stock to admins', function () {
    Product::factory()->inStock(37)->create(['name' => 'Copo']);

    $this->actingAs($this->admin)->get(route('admin.products.index', ['q' => 'cop']))
        ->assertInertia(fn (Assert $page) => $page->where('products.data.0.stock', 37));
});
