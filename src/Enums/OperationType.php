<?php

namespace FilipeFernandes\FeatureFlags\Enums;

enum OperationType
{
    case in;
    case equals;
    case contains;

    public static function values(): array
    {
        return array_map(fn($case) => $case->name, static::cases());
    }
}
