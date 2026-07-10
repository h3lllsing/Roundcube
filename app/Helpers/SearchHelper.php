<?php

namespace App\Helpers;

class SearchHelper
{
    public static function highlight(string $text, string $query): string
    {
        if (empty(trim($query))) {
            return e($text);
        }

        return preg_replace(
            '/(' . preg_quote(trim($query), '/') . ')/iu',
            '<mark class="bg-amber-200 dark:bg-amber-800 rounded px-0.5">$1</mark>',
            e($text)
        );
    }
}
