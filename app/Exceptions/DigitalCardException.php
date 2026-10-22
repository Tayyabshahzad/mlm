<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A business-rule rejection in the digital card workflow. The message is
 * safe to show to the end user; $reason is a stable code for the audit log.
 */
class DigitalCardException extends RuntimeException
{
    public function __construct(string $message, public readonly string $reason = 'rejected')
    {
        parent::__construct($message);
    }
}
