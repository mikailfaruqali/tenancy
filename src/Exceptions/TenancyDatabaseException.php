<?php

namespace Snawbar\Tenancy\Exceptions;

use Exception;

class TenancyDatabaseException extends Exception
{
    public function toArray(): array
    {
        return [
            'error' => TRUE,
            'message' => $this->getMessage(),
        ];
    }
}
