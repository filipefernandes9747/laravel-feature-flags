<?php

if (! function_exists('isFlagActive')) {
    function isFlagActive(array $flag, ?string $environment = null): bool
    {
        if (is_array($flag['enabled'] ?? null)) {
            return ($flag['enabled'][$environment] ?? false) === true;
        }

        return ($flag['enabled'] ?? false) === true;
    }
}
