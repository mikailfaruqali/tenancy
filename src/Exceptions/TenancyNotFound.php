<?php

namespace Snawbar\Tenancy\Exceptions;

use Exception;

class TenancyNotFound extends Exception
{
    public function __construct(
        public readonly string $subdomain
    ) {}

    public function render()
    {
        return view('snawbar-tenancy::404', [
            'subdomain' => $this->subdomain,
        ]);
    }
}
