<?php

namespace App\Exceptions;

use DomainException;

class EmptyCart extends DomainException
{
    public function __construct()
    {
        parent::__construct('O carrinho está vazio.');
    }
}
