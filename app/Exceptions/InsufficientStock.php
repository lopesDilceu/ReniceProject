<?php

namespace App\Exceptions;

use App\Models\Product;
use DomainException;

class InsufficientStock extends DomainException
{
    public function __construct(
        public readonly Product $product,
        public readonly int $requested,
        public readonly int $available,
    ) {
        parent::__construct(
            "Estoque insuficiente para \"{$product->name}\": solicitado {$requested}, disponível {$available}."
        );
    }
}
