<?php

declare(strict_types=1);

namespace Marrow\Ui\Twig;

/**
 * Rewrites `<mui-name ...>...</mui-name>` (and self-closing `<mui-name ... />`)
 * into `{% component 'name' with {...} %}...{% endcomponent %}` (or the
 * function-call form for self-closing tags) — pure text-to-text, before
 * Twig's own lexer ever sees the template. See HtmlComponentLoader, which
 * hooks this into the template-loading pipeline.
 *
 * Attribute syntax:
 *   <mui-button variant="primary">Save</mui-button>
 *       → variant: "primary"                      (plain string)
 *   <mui-button variant="{{ isDanger ? 'danger' : 'primary' }}">Save</mui-button>
 *       → variant: (isDanger ? 'danger' : 'primary')   (the whole value is
 *         one expression — unwrapped, not stringified, so a boolean/array/
 *         number prop keeps its real type, not "true"/"[1,2]" as text)
 *   <mui-button title="Hello {{ name }}!">...</mui-button>
 *       → title: "Hello #{name}!"                  (Twig string interpolation,
 *         for a value that mixes literal text and an expression)
 *   <mui-tabs :tabs="myTabsArray">...</mui-tabs>
 *       → tabs: myTabsArray                        (`:attr` = Vue/Alpine-style
 *         bind — the value is already a Twig expression, used verbatim,
 *         no quotes, no {{ }} needed)
 *   <mui-button disabled>...</mui-button>
 *       → disabled: true                           (boolean attribute, no value)
 *
 * Attribute names are converted from kebab-case to camelCase (`max-width`
 * → `maxWidth`), matching this package's own prop naming.
 *
 * Known limitations (accepted trade-offs, not bugs):
 *   - No awareness of Twig's own `{# comments #}` or `{% verbatim %}`
 *     blocks — literal `<mui-...>` text inside either of those is rewritten
 *     anyway, same as anywhere else. Avoid using the `<mui-` prefix in
 *     templates for anything other than an actual component.
 *   - Malformed markup (an unterminated tag, a stray `/>` with no matching
 *     open) degrades gracefully: the offending fragment is left as literal
 *     text rather than guessed at, so a typo surfaces as "that text shows
 *     up literally on the page" rather than corrupting the rest of the
 *     template.
 */
final class HtmlComponentTag
{
    /**
     * The one line to touch to change the prefix — every length/offset
     * below is derived from it rather than hardcoded, specifically so a
     * future rename (as already happened once: `mu-` → `mui-`) can't
     * silently desync a hardcoded `+N` offset from the string it was
     * actually measuring.
     */
    private const PREFIX = 'mui-';

    public static function rewrite(string $source): string
    {
        $open = '<' . self::PREFIX;

        if (!str_contains($source, $open)) {
            return $source; // overwhelmingly the common case — skip the scan entirely
        }

        $close = '</' . self::PREFIX;
        $closeLen = strlen($close);

        $result = '';
        $pos = 0;
        $len = strlen($source);

        while ($pos < $len) {
            $nextOpen = strpos($source, $open, $pos);
            $nextClose = strpos($source, $close, $pos);

            if ($nextOpen === false && $nextClose === false) {
                $result .= substr($source, $pos);
                break;
            }

            if ($nextClose !== false && ($nextOpen === false || $nextClose < $nextOpen)) {
                if (preg_match('/\G<\/' . preg_quote(self::PREFIX, '/') . '([a-z][a-z0-9-]*)\s*>/i', $source, $m, 0, $nextClose)) {
                    $result .= substr($source, $pos, $nextClose - $pos);
                    $result .= '{% endcomponent %}';
                    $pos = $nextClose + strlen($m[0]);
                    continue;
                }

                // A "</prefix" with no valid closing tag after it — not ours, copy verbatim.
                $result .= substr($source, $pos, $nextClose - $pos + $closeLen);
                $pos = $nextClose + $closeLen;
                continue;
            }

            $result .= substr($source, $pos, $nextOpen - $pos);
            $tag = self::parseTag($source, $nextOpen, $len);
            $result .= $tag['replacement'];
            $pos = $tag['end'];
        }

        return $result;
    }

    /** @return array{end: int, replacement: string} */
    private static function parseTag(string $source, int $start, int $len): array
    {
        if (!preg_match('/\G<' . preg_quote(self::PREFIX, '/') . '([a-z][a-z0-9-]*)/i', $source, $m, 0, $start)) {
            // Unreachable in practice: the caller only arrives here right
            // after finding a literal open-prefix at $start.
            $openLen = strlen(self::PREFIX) + 1;
            return ['end' => $start + $openLen, 'replacement' => substr($source, $start, $openLen)];
        }

        $name = strtolower($m[1]);
        $cursor = $start + strlen($m[0]);
        $attrs = [];

        while (true) {
            while ($cursor < $len && self::isSpace($source[$cursor])) {
                $cursor++;
            }

            if ($cursor >= $len) {
                // Unterminated tag — bail out, leaving the raw text untouched.
                return ['end' => $cursor, 'replacement' => substr($source, $start, $cursor - $start)];
            }

            if ($source[$cursor] === '/' && ($source[$cursor + 1] ?? '') === '>') {
                $cursor += 2;
                return ['end' => $cursor, 'replacement' => self::emitSelfClosing($name, $attrs)];
            }

            if ($source[$cursor] === '>') {
                $cursor++;
                return ['end' => $cursor, 'replacement' => self::emitOpen($name, $attrs)];
            }

            if (!preg_match('/\G(:?[a-zA-Z_][a-zA-Z0-9_-]*)/', $source, $am, 0, $cursor)) {
                // Unexpected character where an attribute name was expected —
                // bail out rather than loop forever or guess.
                return ['end' => $cursor, 'replacement' => substr($source, $start, $cursor - $start)];
            }

            $rawAttrName = $am[1];
            $cursor += strlen($am[0]);

            $afterName = $cursor;
            while ($afterName < $len && self::isSpace($source[$afterName])) {
                $afterName++;
            }

            $dynamic = str_starts_with($rawAttrName, ':');
            $propKey = self::kebabToCamel($dynamic ? substr($rawAttrName, 1) : $rawAttrName);

            if (($source[$afterName] ?? '') === '=') {
                $cursor = $afterName + 1;
                while ($cursor < $len && self::isSpace($source[$cursor])) {
                    $cursor++;
                }
                $quote = $source[$cursor] ?? '';
                if ($quote !== '"' && $quote !== "'") {
                    // Unquoted or missing value — not a shape we support; bail.
                    return ['end' => $cursor, 'replacement' => substr($source, $start, $cursor - $start)];
                }
                $valueStart = $cursor + 1;
                $valueEnd = strpos($source, $quote, $valueStart);
                if ($valueEnd === false) {
                    return ['end' => $valueStart, 'replacement' => substr($source, $start, $valueStart - $start)];
                }
                $rawValue = substr($source, $valueStart, $valueEnd - $valueStart);
                $cursor = $valueEnd + 1;

                $attrs[$propKey] = $dynamic ? trim($rawValue) : self::attributeExpression($rawValue);
            } else {
                // Boolean attribute: no "=", just the bare name.
                $attrs[$propKey] = 'true';
            }
        }
    }

    private static function attributeExpression(string $raw): string
    {
        $trimmed = trim($raw);

        // The whole value is one {{ expr }} — use it unwrapped so the prop
        // keeps its real type (bool/int/array/...), not a stringified copy.
        if (preg_match('/^\{\{(.+)\}\}$/s', $trimmed, $m)) {
            return trim($m[1]);
        }

        // Otherwise, build a Twig double-quoted string: literal segments are
        // escaped, {{ expr }} segments become #{expr} interpolation — so
        // `title="Hello {{ name }}!"` keeps working as one readable attribute
        // rather than forcing `:title="'Hello ' ~ name ~ '!'"`.
        $parts = preg_split('/(\{\{.+?\}\})/s', $raw, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY) ?: [];

        $out = '';
        foreach ($parts as $part) {
            if (preg_match('/^\{\{(.+)\}\}$/s', $part, $pm)) {
                $out .= '#{' . trim($pm[1]) . '}';
            } else {
                $out .= strtr($part, ['\\' => '\\\\', '"' => '\\"']);
            }
        }

        return '"' . $out . '"';
    }

    private static function kebabToCamel(string $name): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name))));
    }

    private static function isSpace(string $char): bool
    {
        return $char === ' ' || $char === "\t" || $char === "\n" || $char === "\r";
    }

    /** @param array<string, string> $attrs */
    private static function emitOpen(string $name, array $attrs): string
    {
        $with = self::buildWithClause($attrs);
        return "{% component '{$name}'{$with} %}";
    }

    /** @param array<string, string> $attrs */
    private static function emitSelfClosing(string $name, array $attrs): string
    {
        return "{{ component('{$name}', " . self::buildHash($attrs) . ') }}';
    }

    /** @param array<string, string> $attrs */
    private static function buildWithClause(array $attrs): string
    {
        return $attrs === [] ? '' : ' with ' . self::buildHash($attrs);
    }

    /** @param array<string, string> $attrs */
    private static function buildHash(array $attrs): string
    {
        if ($attrs === []) {
            return '{}';
        }

        $pairs = [];
        foreach ($attrs as $key => $expr) {
            $pairs[] = "{$key}: {$expr}";
        }

        return '{' . implode(', ', $pairs) . '}';
    }
}
