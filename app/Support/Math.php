<?php

namespace App\Support;

final class Math
{
    public static function compareMoney($left, $right, int $scale = 2): int
    {
        if (function_exists('bccomp')) {
            return bccomp((string) $left, (string) $right, $scale);
        }

        $diff = round((float) $left - (float) $right, $scale);
        if ($diff == 0.0) {
            return 0;
        }
        return $diff < 0 ? -1 : 1;
    }
}
