<?php

namespace App\Payments;

use RuntimeException;

/**
 * The provider could not be reached or refused the request. Callers decide
 * whether to retry (queued jobs) or let the customer try again (checkout).
 */
class PaymentGatewayException extends RuntimeException {}
