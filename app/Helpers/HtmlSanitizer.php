<?php

namespace App\Helpers;

class HtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'p', 'br', 'hr',
        'ul', 'ol', 'li',
        'strong', 'em', 'b', 'i', 'u',
        'code', 'pre',
        'blockquote',
        'div', 'span',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
        'a', 'img',
        'dl', 'dt', 'dd',
        'abbr', 'cite', 'dfn',
    ];

    public static function sanitize(string $html): string
    {
        $html = preg_replace('/\s+on\w+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);

        $html = preg_replace('/(href|src)\s*=\s*(?:"\s*javascript:[^"]*"|\'\s*javascript:[^\']*\')/i', '$1="#"', $html);

        $allowed = '<' . implode('><', self::ALLOWED_TAGS) . '>';
        $html = strip_tags($html, $allowed);

        return $html;
    }
}
