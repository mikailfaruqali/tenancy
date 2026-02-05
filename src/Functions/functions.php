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

function tenancy_asset(string $path): string
{
    return sprintf('/%s/%s/%s',
        config()->string('snawbar-tenancy.symlink'),
        request()->getHost(),
        $path,
    );
}
