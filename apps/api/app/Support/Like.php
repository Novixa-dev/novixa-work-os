<?php

namespace App\Support;

/**
 * Helpers for building safe SQL LIKE patterns from user input.
 */
final class Like
{
    /** Escape LIKE wildcards (percent, underscore, backslash) so input matches literally. */
    public static function escape(string $value): string
    {
        return addcslashes($value, '%_'.chr(92));
    }

    /** Build a "contains" pattern with wildcards escaped. */
    public static function contains(string $value): string
    {
        return '%'.self::escape($value).'%';
    }
}
