<?php

namespace Snawbar\Tenancy\Exceptions;

use Exception;

class TenancyAlreadyExists extends Exception
{
    public function __construct(string $subdomain)
    {
        parent::__construct(sprintf('Tenant already exists: %s', $subdomain));
    }
}
