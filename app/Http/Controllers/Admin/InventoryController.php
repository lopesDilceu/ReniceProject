<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Inventory\RecordInventoryMovement;
use App\Enums\InventoryMovementType;
use App\Exceptions\InsufficientStock;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InventoryAdjustmentRequest;
use App\Http\Resources\Admin\AdminProductResource;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * A product's stock history and manual adjustments (e.g. a physical count).
 */
class InventoryController extends Controller
{
    public function show(Request $request, Product $product): Response
    {
        $movements = $product->inventoryMovements()
            ->with('user:id,name')
            ->latest('id')
            ->paginate(25);

        return Inertia::render('admin/inventory/show', [
            'product' => (new AdminProductResource($product))->resolve($request),
            'movements' => $movements->through(fn (InventoryMovement $movement) => [
                'id' => $movement->id,
                'type' => $movement->type->value,
                'type_label' => $movement->type->label(),
                'quantity' => $movement->quantity,
                'stock_after' => $movement->stock_after,
                'source' => $movement->source_type === null ? null : class_basename($movement->source_type).' #'.$movement->source_id,
                'user' => $movement->user?->name,
                'note' => $movement->note,
                'created_at' => $movement->created_at->toIso8601String(),
            ]),
        ]);
    }

    public function store(InventoryAdjustmentRequest $request, Product $product, RecordInventoryMovement $record): RedirectResponse
    {
        try {
            $record->handle(
                $product,
                InventoryMovementType::Adjustment,
                $request->integer('quantity'),
                actor: $request->user(),
                note: $request->validated('note'),
            );
        } catch (InsufficientStock $exception) {
            throw ValidationException::withMessages(['quantity' => $exception->getMessage()]);
        }

        $this->toast('success', 'Estoque ajustado.');

        return back();
    }
}
