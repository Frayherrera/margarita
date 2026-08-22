<?php

if (! function_exists('money')) {
    /** Formato COP: 1250000 -> "$1.250.000" */
    function money(float|string|null $value): string
    {
        return '$'.number_format((float) $value, 0, ',', '.');
    }
}

if (! function_exists('qty')) {
    /** 4.00 -> "4", 2.50 -> "2,5" */
    function qty(float|string|null $value): string
    {
        $number = (float) $value;

        return rtrim(rtrim(number_format($number, 2, ',', '.'), '0'), ',');
    }
}
