<?php

namespace Snawbar\Tenancy\Exceptions;

use Exception;

class TenancyNotFound extends Exception
{
    public function render()
    {
        return view('errors.custom.unknown', [
            'message' => __('errors.nadozrayawa'),
            'code' => 404,
        ]);
    }
}
