<?php
function minify($path)
{
    $file_type = pathinfo($path, PATHINFO_EXTENSION);

    $cache_dir = $_SERVER['DOCUMENT_ROOT'] . '/cache/minified/' . $file_type;

    if (!is_dir($cache_dir))
        mkdir($cache_dir, 0755, true);

    $cache_file = $cache_dir . '/' . basename($path);

    if (!file_exists($cache_file) || filemtime($path) > filemtime($cache_file)) {
        $code = file_get_contents($path);

        if ($file_type === 'css') {
            // remove CSS comments (`/* ... */`).
            $code = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $code);
            // remove tabs, newlines, and carriage returns.
            $code = str_replace(["\r\n", "\r", "\n", "\t"], '', $code);
            // remove whitespace after colons.
            $code = str_replace(': ', ':', $code);
            // remove whitespace around selectors, braces, and other operators.
            $code = preg_replace('/\s*([,;{}])\s*/', '$1', $code);
        } else if ($file_type === 'js') {
            // remove multi-line comments (`/* ... */`).
            $code = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $code);
            // remove single-line comments (`// ...`), being careful not to remove URLs.
            $code = preg_replace('/(?<!:)\s*\/\/.*$/m', '', $code);
            // Regex to match all valid string literals (single-quoted, double-quoted, and template literals).
            // This handles escaped characters within the strings.
            $string_regex = '/(`(?:\\\\.|[^`\\\\])*`|\'(?:\\\\.|[^\'\\\\])*\'|"(?:\\\\.|[^"\\\\])*")/s';
            // Split the code by strings, keeping the strings as captured delimiters.
            $parts = preg_split($string_regex, $code, -1, PREG_SPLIT_DELIM_CAPTURE);

            $minified_code = '';
            foreach ($parts as $i => $part) {
                // Even-indexed parts are code, odd-indexed are strings.
                if ($i % 2 === 0) {
                    // This is a code segment. Apply minification.
                    // 1. Collapse all whitespace sequences (spaces, tabs, newlines) to a single space.
                    $part = preg_replace('/\s+/', ' ', $part);
                    // 2. Remove whitespace around operators like ',', ';', '{', '}', '(', ')', '='.
                    $part = preg_replace('/\s*([,;{}()=])\s*/', '$1', $part);
                }
                // Append the processed part (or the original, untouched string) to the result.
                $minified_code .= $part;
            }
            $code = $minified_code;
        }

        $tmp = tempnam(dirname($cache_file), 'min__');
        file_put_contents($tmp, trim($code));
        rename($tmp, $cache_file);
    }
    return file_get_contents($cache_file);
}