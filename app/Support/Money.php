<?php

namespace App\Support;

class Money
{
    public static function fmt(int $tiyin, string $cur = 'UZS'): string
    {
        return number_format($tiyin / 100, 2, ',', ' ').' '.($cur === 'UZS' ? "so'm" : $cur);
    }

    /** "1 234,50" / "1234.5" -> tiyin yoki null */
    public static function parse(string $v): ?int
    {
        $v = str_replace([' ', ','], ['', '.'], trim($v));
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $v)) {
            return null;
        }
        return (int) round(((float) $v) * 100);
    }
}
