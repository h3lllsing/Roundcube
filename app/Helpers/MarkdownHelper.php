<?php

namespace App\Helpers;

use App\Helpers\HtmlSanitizer;

class MarkdownHelper
{
    private static array $codeBlocks = [];

    public static function toHtml(string $markdown): string
    {
        self::$codeBlocks = [];
        $html = $markdown;
        $html = self::extractCodeBlocks($html);
        $html = self::blockRender($html);
        $html = self::restoreCodeBlocks($html);
        return HtmlSanitizer::sanitize($html);
    }

    private static array $genericTocHeadings = [
        'purpose', 'when to use', 'permission required', 'permissions required',
        'step-by-step workflow', 'best practices', 'common mistakes',
        'typical business scenario', 'expected result', 'available templates',
        'what you cannot do', 'related pages', 'table of contents', 'overview',
    ];

    public static function extractHeadings(string $markdown, bool $filterGeneric = true): array
    {
        $headings = [];
        $lines = explode("\n", $markdown);
        $sectionStack = [];
        $inCodeBlock = false;
        $seenIds = [];

        foreach ($lines as $line) {
            if (preg_match('/^```/', trim($line))) {
                $inCodeBlock = !$inCodeBlock;
                continue;
            }
            if ($inCodeBlock) continue;
            if (trim($line) === '') continue;

            if (!preg_match('/^(#{1,4})\s+(.+)$/', $line, $m)) continue;

            $level = strlen($m[1]);
            $text = trim($m[2]);
            $slug = self::makeSlug($text);
            $slug = trim($slug, '-');

            if ($slug === '' || $slug === '-') continue;

            $sectionStack = array_slice($sectionStack, 0, $level - 1);
            $sectionStack[$level - 1] = $slug;

            $id = self::makeHeadingId($slug, $level, $sectionStack);

            if (in_array($id, $seenIds)) {
                $counter = 2;
                $baseId = $id;
                while (in_array($baseId . '-' . $counter, $seenIds)) {
                    $counter++;
                }
                $id = $baseId . '-' . $counter;
            }
            $seenIds[] = $id;

            if ($filterGeneric && $level >= 3 && in_array(strtolower($text), self::$genericTocHeadings)) {
                continue;
            }

            $headings[] = ['level' => $level, 'text' => $text, 'id' => $id];
        }
        return $headings;
    }

    private static function makeSlug(string $text): string
    {
        $text = strip_tags($text);
        $text = strtolower($text);
        $result = '';
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $c = $text[$i];
            if (($c >= 'a' && $c <= 'z') || ($c >= '0' && $c <= '9')) {
                $result .= $c;
            } elseif ($result !== '' && $result[-1] !== '-') {
                $result .= '-';
            }
        }
        return trim($result, '-');
    }

    private static function makeHeadingId(string $slug, int $level, array $stack): string
    {
        if ($level <= 2) return $slug;
        $parts = [];
        if (!empty($stack[1])) $parts[] = $stack[1];
        if ($level >= 4 && !empty($stack[2])) $parts[] = $stack[2];
        $parts[] = $slug;
        return implode('-', $parts);
    }

    private static function extractCodeBlocks(string $html): string
    {
        return preg_replace_callback('/```(\w*)\s*\n(.*?)```/s', function ($m) {
            $index = count(self::$codeBlocks);
            $lang = $m[1] ? ' class="language-' . $m[1] . '"' : '';
            $code = $m[2];
            $html = '<pre><code' . $lang . '>' . htmlspecialchars($code) . '</code></pre>';
            self::$codeBlocks[] = $html;
            return "%%%CODEBLOCK{$index}%%%";
        }, $html);
    }

    private static function restoreCodeBlocks(string $html): string
    {
        foreach (self::$codeBlocks as $i => $block) {
            $html = str_replace("%%%CODEBLOCK{$i}%%%", $block, $html);
        }
        return $html;
    }

    private static function blockRender(string $html): string
    {
        return (new MarkdownRenderer)->render($html);
    }
}
