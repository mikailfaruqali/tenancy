<?php

function formatHealthValue($value): ?string
{
    if (blank($value)) {
        return NULL;
    }

    if (is_numeric($value)) {
        return number_format($value);
    }

    if (strtotime((string) $value)) {
        return date('Y-m-d', strtotime((string) $value));
    }

    return (string) $value;
}
