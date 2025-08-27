<?php

namespace FilipeFernandes\FeatureFlags\Enums;

enum ContextType
{
    case user;
    case context;

    public static function values(): array
    {
        return array_map(fn($case) => $case->name, self::cases());
    }
}
