<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\ActsForCustomer;
use App\Models\CartItem;
use App\Support\CheckoutConfirmation;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description(<<<'TEXT'
Prepare an order from the cart, for the given address. This does NOT place the order: it returns a summary and a `confirmation_url`. Show the summary to the customer and ask them to open the link; the order is placed only when they confirm it in the store, signed in to their account. The link expires in 15 minutes and stops working if the cart changes. After they confirm, list-orders shows the order and its Pix code.
TEXT)]
class StartCheckout extends WriteTool
{
    use ActsForCustomer;

    public function handle(Request $request): Response|ResponseFactory
    {
        $customer = $this->customer($request);

        $input = $request->validate([
            'address_id' => ['required', 'integer'],
        ]);

        $address = $customer->addresses()->find($input['address_id']);

        if ($address === null) {
            return Response::error('That address is not one of the customer\'s. Use list-addresses.');
        }

        if ($customer->email_verified_at === null) {
            return Response::error('The customer must confirm their e-mail address before ordering.');
        }

        $items = $customer->cart?->items()->with('product')->get() ?? new Collection;

        if ($items->isEmpty()) {
            return Response::error('The cart is empty.');
        }

        $unavailable = $items
            ->reject(fn (CartItem $item) => $item->product->isAvailable())
            ->map(fn (CartItem $item) => $item->product->name);

        if ($unavailable->isNotEmpty()) {
            return Response::error('Out of stock, remove before checkout: '.$unavailable->implode(', ').'.');
        }

        return Response::structured([
            'status' => 'awaiting_customer_confirmation',
            'confirmation_url' => CheckoutConfirmation::url($customer, $address),
            'expires_at' => CheckoutConfirmation::expiresAt()->toIso8601String(),
            'cart' => ViewCart::cart($customer),
            'shipping_address' => $address->only(['street', 'number', 'complement', 'neighborhood', 'city', 'state', 'cep']),
            'payment' => 'Pix, shown after the customer confirms.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'address_id' => $schema->integer()->description('One of the ids from list-addresses.')->required(),
        ];
    }
}
