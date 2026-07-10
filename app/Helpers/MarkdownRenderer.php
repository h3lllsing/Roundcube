<?php

namespace App\Helpers;

class MarkdownRenderer
{
    private array $sectionStack = [];
    private array $seenIds = [];

    public function render(string $html): string
    {
        $lines = explode("\n", $html);
        $result = [];
        $i = 0;
        $count = count($lines);

        while ($i < $count) {
            $line = $lines[$i];

            if ($this->handleEmptyLine($line, $result, $i)) continue;
            if ($this->handleCodeBlock($line, $result, $i)) continue;
            if ($this->handleHeading($line, $result, $i, $lines)) continue;
            if ($this->handleHorizontalRule($line, $result, $i)) continue;
            if ($this->handleToc($line, $i)) continue;
            if ($this->handleComment($line, $i)) continue;
            if ($this->handleTableOfContentsHeader($line, $i)) continue;
            if ($this->handleFrontmatter($line, $i)) continue;
            if ($this->handleTable($lines, $i, $result)) continue;
            if ($this->handleBlockquote($lines, $i, $result)) continue;
            if ($this->handleList($lines, $i, $result)) continue;
            if ($this->handleParagraph($lines, $i, $result)) continue;

            $i++;
        }

        return implode("\n", $result);
    }

    private function handleEmptyLine(string $line, array &$result, int &$i): bool
    {
        if (trim($line) !== '') {
            return false;
        }
        $result[] = '';
        $i++;
        return true;
    }

    private function handleCodeBlock(string $line, array &$result, int &$i): bool
    {
        if (!str_starts_with(trim($line), '%%%CODEBLOCK')) {
            return false;
        }
        $result[] = $line;
        $i++;
        return true;
    }

    private function handleHeading(string $line, array &$result, int &$i, array $lines): bool
    {
        if (!preg_match('/^(#{1,4})\s+(.+)$/', $line, $m)) {
            return false;
        }
        $level = strlen($m[1]);
        $text = trim($m[2]);
        $slug = $this->makeSlug($text);
        $this->sectionStack = array_slice($this->sectionStack, 0, $level - 1);
        $this->sectionStack[$level - 1] = $slug;
        $id = $this->makeHeadingId($slug, $level, $this->sectionStack);
        if (in_array($id, $this->seenIds)) {
            $counter = 2;
            $baseId = $id;
            while (in_array($baseId . '-' . $counter, $this->seenIds)) {
                $counter++;
            }
            $id = $baseId . '-' . $counter;
        }
        $this->seenIds[] = $id;
        $sizes = ['2xl', 'xl', 'lg', 'base'];
        $mt = ['8', '6', '5', '4'];
        $renderedText = self::renderInline($text);
        $result[] = "<h{$level} id=\"{$id}\" class=\"text-{$sizes[$level-1]} font-bold mt-{$mt[$level-1]} mb-3 text-gray-900 dark:text-white\">{$renderedText}</h{$level}>";
        $i++;
        return true;
    }

    private function handleHorizontalRule(string $line, array &$result, int &$i): bool
    {
        if (!preg_match('/^(-{3,}|_{3,}|\*{3,})\s*$/', trim($line))) {
            return false;
        }
        $result[] = '<hr class="my-8 border-gray-200 dark:border-gray-700">';
        $i++;
        return true;
    }

    private function handleToc(string $line, int &$i): bool
    {
        if (!preg_match('/^\[\[_TOC_\]\]/', $line)) {
            return false;
        }
        $i++;
        return true;
    }

    private function handleComment(string $line, int &$i): bool
    {
        if (!preg_match('/^\[comment\]/', trim($line))) {
            return false;
        }
        $i++;
        return true;
    }

    private function handleTableOfContentsHeader(string $line, int &$i): bool
    {
        if (!preg_match('/^Table of Contents|^- \[/', $line) || $i >= 5) {
            return false;
        }
        $i++;
        return true;
    }

    private function handleFrontmatter(string $line, int &$i): bool
    {
        if (!preg_match('/^---\s*$/', $line)) {
            return false;
        }
        $i++;
        return true;
    }

    private function handleTable(array $lines, int &$i, array &$result): bool
    {
        if (!self::isTableLine($lines[$i])) {
            return false;
        }
        $tableResult = self::collectTable($lines, $i);
        $result[] = $tableResult['html'];
        $i = $tableResult['nextLine'];
        return true;
    }

    private function handleBlockquote(array $lines, int &$i, array &$result): bool
    {
        if (!str_starts_with(ltrim($lines[$i]), '>')) {
            return false;
        }
        $bqResult = self::collectBlockquote($lines, $i);
        $result[] = $bqResult['html'];
        $i = $bqResult['nextLine'];
        return true;
    }

    private function handleList(array $lines, int &$i, array &$result): bool
    {
        if (!self::isListLine($lines[$i])) {
            return false;
        }
        $listResult = self::collectList($lines, $i);
        $result[] = $listResult['html'];
        $i = $listResult['nextLine'];
        return true;
    }

    private function handleParagraph(array $lines, int &$i, array &$result): bool
    {
        $paragraph = [];
        $count = count($lines);
        while ($i < $count) {
            $pl = $lines[$i];
            if (trim($pl) === '') break;
            if (self::isBlockStart($pl)) break;
            $paragraph[] = $pl;
            $i++;
        }
        if (empty($paragraph)) {
            return false;
        }
        $text = implode("\n", $paragraph);
        $text = self::renderInline($text);
        $result[] = '<p class="mb-4 text-gray-600 dark:text-gray-300 leading-relaxed">' . $text . '</p>';
        return true;
    }

    private static function isBlockStart(string $line): bool
    {
        $t = trim($line);
        if (preg_match('/^(#{1,4})\s/', $t)) return true;
        if (str_starts_with($t, '%%%CODEBLOCK')) return true;
        if (self::isTableLine($t)) return true;
        if (str_starts_with(ltrim($t), '>')) return true;
        if (self::isListLine($t)) return true;
        if (preg_match('/^(-{3,}|_{3,}|\*{3,})\s*$/', $t)) return true;
        return false;
    }

    private static function isTableLine(string $line): bool
    {
        return preg_match('/^\|.+\|$/', trim($line)) && substr_count($line, '|') >= 2;
    }

    private static function isListLine(string $line): bool
    {
        $t = ltrim($line);
        if (preg_match('/^[-*+]\s+/', $t)) return true;
        if (preg_match('/^\d+\.\s+/', $t)) return true;
        return false;
    }

    private static function collectTable(array $lines, int $start): array
    {
        $tableLines = [];
        $i = $start;
        $count = count($lines);
        while ($i < $count && self::isTableLine($lines[$i])) {
            $tableLines[] = trim($lines[$i]);
            $i++;
        }
        if (count($tableLines) < 3) {
            return ['html' => implode("\n", $tableLines), 'nextLine' => $i];
        }

        $headerCells = self::parseRow($tableLines[0]);
        $alignments = self::parseAlignments($tableLines[1], count($headerCells));
        $bodyRows = [];
        for ($j = 2; $j < count($tableLines); $j++) {
            $bodyRows[] = self::parseRow($tableLines[$j]);
        }

        $html = '<div class="overflow-x-auto my-4"><table class="min-w-full text-sm border-collapse border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">';
        $html .= '<thead><tr class="bg-gray-50 dark:bg-gray-800">';
        foreach ($headerCells as $idx => $cell) {
            $align = $alignments[$idx] ?? 'left';
            $html .= "<th class=\"px-4 py-2.5 text-{$align} font-semibold text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700\">{$cell}</th>";
        }
        $html .= '</tr></thead><tbody>';
        foreach ($bodyRows as $row) {
            $html .= '<tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50/50 dark:hover:bg-gray-800/30">';
            foreach ($row as $idx => $cell) {
                $align = $alignments[$idx] ?? 'left';
                $html .= "<td class=\"px-4 py-2.5 text-{$align} text-gray-600 dark:text-gray-300\">{$cell}</td>";
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';

        return ['html' => $html, 'nextLine' => $i];
    }

    private static function parseRow(string $line): array
    {
        $line = trim($line);
        $line = trim($line, '|');
        $cells = explode('|', $line);
        return array_map(function ($c) {
            $c = trim($c);
            $c = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $c);
            $c = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $c);
            $c = preg_replace('/`([^`]+)`/', '<code class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-pink-600 dark:text-pink-400 text-xs font-mono">$1</code>', $c);
            $c = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" class="text-indigo-600 dark:text-indigo-400 underline underline-offset-2">$1</a>', $c);
            return $c;
        }, $cells);
    }

    private static function parseAlignments(string $separator, int $count): array
    {
        $cells = self::parseRow($separator);
        $alignments = [];
        foreach ($cells as $cell) {
            $cell = trim($cell);
            if (str_starts_with($cell, ':') && str_ends_with($cell, ':')) {
                $alignments[] = 'center';
            } elseif (str_ends_with($cell, ':')) {
                $alignments[] = 'right';
            } else {
                $alignments[] = 'left';
            }
        }
        while (count($alignments) < $count) {
            $alignments[] = 'left';
        }
        return $alignments;
    }

    private static function collectBlockquote(array $lines, int $start): array
    {
        $bqLines = [];
        $i = $start;
        $count = count($lines);
        while ($i < $count && str_starts_with(ltrim($lines[$i]), '>')) {
            $text = ltrim($lines[$i]);
            $text = ltrim($text, '> ');
            $bqLines[] = $text;
            $i++;
        }
        $content = implode("\n", $bqLines);

        $isCallout = preg_match('/^\[!(\w+)\]/', $content, $cm);
        if ($isCallout) {
            $type = strtolower($cm[1]);
            $content = trim(substr($content, strlen($cm[0])));
            $typeClass = match($type) {
                'tip', 'success' => 'callout-tip',
                'warning', 'caution' => 'callout-warning',
                'note', 'info' => 'callout-info',
                default => 'callout-info',
            };
            $icon = match($type) {
                'tip', 'success' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                'warning', 'caution' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z',
                default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            };
            $content = self::renderInline($content);
            $html = '<div class="callout ' . $typeClass . '">';
            $html .= '<svg class="callout-icon w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="' . $icon . '"/></svg>';
            $html .= '<div>' . $content . '</div></div>';
            return ['html' => $html, 'nextLine' => $i];
        }

        $content = self::renderInline($content);
        $html = '<blockquote class="border-l-4 border-indigo-400 dark:border-indigo-500 pl-4 py-3 my-4 bg-indigo-50/50 dark:bg-indigo-950/30 text-gray-700 dark:text-gray-300 rounded-r-lg">';
        $html .= $content . '</blockquote>';
        return ['html' => $html, 'nextLine' => $i];
    }

    private static function collectList(array $lines, int $start): array
    {
        $items = [];
        $i = $start;
        $count = count($lines);
        $isOrdered = false;
        $minIndent = null;

        while ($i < $count) {
            $line = $lines[$i];
            if (trim($line) === '') {
                $i++;
                break;
            }
            if (self::isBlockStart($line) && !self::isListLine($line)) break;

            if (self::isListLine($line)) {
                $indent = strlen($line) - strlen(ltrim($line));
                if ($minIndent === null) $minIndent = $indent;
                if ($indent > $minIndent && !empty($items)) {
                    $items[count($items)-1]['sub'][] = $line;
                } else {
                    $isOrdered = $isOrdered || preg_match('/^\d+\.\s+/', ltrim($line));
                    $items[] = ['line' => $line, 'sub' => []];
                }
                $i++;
            } elseif (!empty($items)) {
                $items[count($items)-1]['sub'][] = $line;
                $i++;
            } else {
                break;
            }
        }

        $html = $isOrdered ? '<ol class="list-decimal pl-6 my-4 space-y-2">' : '<ul class="list-disc pl-6 my-4 space-y-2">';
        foreach ($items as $item) {
            $text = ltrim($item['line']);
            if ($isOrdered) {
                $text = preg_replace('/^\d+\.\s*/', '', $text);
            } else {
                $text = preg_replace('/^[-*+]\s*/', '', $text);
            }
            $text = self::renderInline($text);
            $html .= '<li class="text-gray-600 dark:text-gray-300 leading-relaxed">' . $text;
            if (!empty($item['sub'])) {
                $html .= '<ul class="list-circle pl-5 mt-1 space-y-1">';
                foreach ($item['sub'] as $subLine) {
                    $subText = ltrim($subLine);
                    $subText = preg_replace('/^[-*+]\s*/', '', $subText);
                    $subText = self::renderInline($subText);
                    $html .= '<li class="text-gray-500 dark:text-gray-400 text-sm">' . $subText . '</li>';
                }
                $html .= '</ul>';
            }
            $html .= '</li>';
        }
        $html .= $isOrdered ? '</ol>' : '</ul>';

        return ['html' => $html, 'nextLine' => $i];
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

    public static function renderInline(string $text): string
    {
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong class="font-semibold text-gray-900 dark:text-white">$1</strong>', $text);
        $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
        $text = preg_replace('/`([^`]+)`/', '<code class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-pink-600 dark:text-pink-400 text-sm font-mono">$1</code>', $text);
        $text = preg_replace('/\[([^\]]+)\]\(#([^)]+)\)/', '<a href="#$2" class="text-indigo-600 dark:text-indigo-400 hover:underline underline-offset-2">$1</a>', $text);
        $text = preg_replace('/\[([^\]]+)\]\(([^#][^)]+)\)/', '<a href="$2" class="text-indigo-600 dark:text-indigo-400 hover:underline underline-offset-2">$1</a>', $text);
        $text = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1" class="max-w-full h-auto rounded-lg my-4 shadow-md" loading="lazy">', $text);
        return $text;
    }
}
