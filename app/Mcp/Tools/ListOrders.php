<?php

namespace App\Mcp\Tools;

use App\Http\Resources\Api\V1\OrderResource;
use App\Mcp\Tools\Concerns\ActsForCustomer;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Description("List the customer's orders, newest first, or show one in full with `order_id`: items, timeline and, while unpaid, the Pix copy-and-paste code.")]
class ListOrders extends Tool
{
    use ActsForCustomer;

    public function handle(Request $request): Response|ResponseFactory
    {
        $input = $request->validate([
            'order_id' => ['nullable', 'integer'],
        ]);

        $orders = $this->customer($request)->orders();

        if (isset($input['order_id'])) {
            // Scoped to the customer: another customer's order is "not found".
            $order = $orders->with(['items', 'statusChanges', 'payment'])->find($input['order_id']);

            return $order === null
                ? Response::error("Order #{$input['order_id']} not found.")
                : Response::structured(['order' => (new OrderResource($order))->resolve(request())]);
        }

        return Response::structured([
            'orders' => OrderResource::collection($orders->withCount('items')->latest('id')->limit(10)->get())->resolve(request()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'order_id' => $schema->integer()->description('Show this order in full.'),
        ];
    }
}
