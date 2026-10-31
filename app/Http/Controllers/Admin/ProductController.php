<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Resources\Admin\AdminProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('q'));
        $removed = $request->boolean('removed');

        $products = Product::query()
            ->when($removed, fn (Builder $query) => $query->onlyTrashed())
            ->when($search !== '', fn (Builder $query) => $query->whereLike('name', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/products/index', [
            'products' => AdminProductResource::collection($products),
            'filters' => ['q' => $search, 'removed' => $removed],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/products/form', ['product' => null]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = Product::create([
            'name' => $request->validated('name'),
            'slug' => Product::uniqueSlug($request->validated('name')),
            'description' => $request->validated('description'),
            'price_cents' => $request->validated('price_cents'),
            'image_path' => $request->file('image')?->store('products', 'public'),
        ]);

        $this->toast('success', "\"{$product->name}\" cadastrado. Registre uma compra para colocá-lo em estoque.");

        return to_route('admin.products.index');
    }

    public function edit(Request $request, Product $product): Response
    {
        return Inertia::render('admin/products/form', [
            'product' => (new AdminProductResource($product))->resolve($request),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->fill([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'price_cents' => $request->validated('price_cents'),
        ]);

        if ($request->hasFile('image')) {
            $previous = $product->image_path;
            $product->image_path = $request->file('image')->store('products', 'public');

            if ($previous) {
                Storage::disk('public')->delete($previous);
            }
        }

        $product->save();

        $this->toast('success', 'Produto atualizado.');

        return to_route('admin.products.index');
    }

    /**
     * Soft delete: the product leaves the catalog, its history stays.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        $this->toast('success', "\"{$product->name}\" removido do catálogo.");

        return back();
    }

    public function restore(Product $product): RedirectResponse
    {
        $product->restore();

        $this->toast('success', "\"{$product->name}\" de volta ao catálogo.");

        return back();
    }
}
