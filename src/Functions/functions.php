<?php

function formatHealthValue($value): string
{
    if (is_numeric($value)) {
        return number_format($value);
    }

    if (strtotime((string) $value)) {
        return date('Y-m-d', strtotime((string) $value));
    }

    return $value;
}
