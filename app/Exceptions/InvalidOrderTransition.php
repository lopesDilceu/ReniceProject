<?php

namespace App\Exceptions;

use App\Enums\OrderStatus;
use DomainException;

class InvalidOrderTransition extends DomainException
{
    public static function notAllowed(OrderStatus $from, OrderStatus $to): self
    {
        return new self("Um pedido \"{$from->label()}\" não pode passar para \"{$to->label()}\".");
    }

    public static function reasonRequired(OrderStatus $to): self
    {
        return new self("Informe o motivo para marcar o pedido como \"{$to->label()}\".");
    }
}
