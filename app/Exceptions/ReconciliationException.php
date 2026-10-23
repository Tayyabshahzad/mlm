<?php

namespace App\Exceptions;

use RuntimeException;

/** A bank-reconciliation rule rejection; the message is safe to show to staff. */
class ReconciliationException extends RuntimeException
{
    public function __construct(string $message, public readonly string $reason = 'rejected')
    {
        parent::__construct($message);
    }
}
