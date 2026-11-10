<?php

namespace App\Support;

use Illuminate\Http\Request;

final class Pagination
{
    public const MAX_PER_PAGE = 100;

    /** Read `per_page` from the request, clamped to 1..MAX_PER_PAGE so clients cannot request unbounded pages. */
    public static function perPage(Request $request, int $default = 25): int
    {
        return max(1, min((int) $request->input('per_page', $default), self::MAX_PER_PAGE));
    }
}
