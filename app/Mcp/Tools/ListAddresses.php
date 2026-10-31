<?php

namespace App\Mcp\Tools;

use App\Http\Resources\Api\V1\AddressResource;
use App\Mcp\Tools\Concerns\ActsForCustomer;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Description("List the customer's saved delivery addresses. start-checkout needs one of their ids.")]
class ListAddresses extends Tool
{
    use ActsForCustomer;

    public function handle(Request $request): ResponseFactory
    {
        return Response::structured([
            'addresses' => AddressResource::collection($this->customer($request)->addresses()->orderBy('id')->get())->resolve(request()),
        ]);
    }
}
