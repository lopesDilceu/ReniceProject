<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Inventory\RecordSupplierPurchase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SupplierPurchaseRequest;
use App\Models\Product;
use App\Models\SupplierPurchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class SupplierPurchaseController extends Controller
{
    public function index(): Response
    {
        $purchases = SupplierPurchase::query()
            ->with('product:id,name')
            ->latest('purchased_at')
            ->latest('id')
            ->paginate(20);

        return Inertia::render('admin/purchases/index', [
            'purchases' => $purchases->through(fn (SupplierPurchase $purchase) => [
                'id' => $purchase->id,
                'product' => $purchase->product->name,
                'supplier' => $purchase->supplier,
                'quantity' => $purchase->quantity,
                'unit_cost_cents' => $purchase->unit_cost_cents,
                'total_cents' => $purchase->quantity * $purchase->unit_cost_cents,
                'purchased_at' => $purchase->purchased_at->toIso8601String(),
            ]),
            'products' => Product::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(SupplierPurchaseRequest $request, RecordSupplierPurchase $record): RedirectResponse
    {
        $record->handle(
            Product::query()->findOrFail($request->integer('product_id')),
            supplier: $request->validated('supplier'),
            quantity: $request->integer('quantity'),
            unitCostCents: (int) $request->validated('unit_cost_cents'),
            purchasedAt: $request->filled('purchased_at') ? Carbon::parse($request->validated('purchased_at')) : null,
            actor: $request->user(),
        );

        $this->toast('success', 'Compra registrada e estoque atualizado.');

        return back();
    }
}
