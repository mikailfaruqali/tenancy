<?php

namespace Snawbar\Tenancy\Exceptions;

use Exception;

class DatabaseCopyFailed extends Exception
{
    public function __construct(string $from, string $to, string $error)
    {
        parent::__construct(sprintf('Failed to copy database from %s to %s: %s', $from, $to, $error));
    }
}
