<?php
namespace App\Modules\Importer\Support;

/**
 * Reduces arbitrary HTML (scraped from a third-party page, or otherwise)
 * down to a small, safe allowlist — bold, italic, line breaks, paragraphs
 * — for the one place in this app that stores real markup instead of
 * plain text: catalog_toy_descriptions.description. Every attribute is
 * dropped even on kept tags (no style/onclick/etc. survives), and any
 * other tag is replaced with just its own text content rather than
 * removed outright, so a stray image or link contributes nothing but
 * doesn't eat surrounding text either.
 *
 * This is the authoritative sanitization boundary — called here in
 * ImporterRunController regardless of which driver produced the text, so
 * a driver forgetting to sanitize (or a future driver copy-pasted without
 * it) can never result in unsafe HTML reaching the database. A driver
 * that builds its own description HTML may also call this directly if it
 * needs a sanitized value earlier (e.g. to combine several sections), but
 * doing so is a convenience, not a substitute for this class's own
 * enforcement at storage time.
 *
 * Written by hand rather than pulling in a sanitizer library, since this
 * app has no dependency manager (no composer.json/vendor) at all.
 */
class DescriptionSanitizer
{
    private const ALLOWED_TAGS = ['b' => 'b', 'strong' => 'b', 'i' => 'i', 'em' => 'i', 'br' => 'br', 'p' => 'p'];

    // Tags whose CONTENT is never text meant for display — dropped
    // entirely (tag and content both), unlike other disallowed tags
    // (e.g. a stray <a> or <img>) which keep their inner text.
    private const DROP_ENTIRELY = ['script' => true, 'style' => true];

    public static function sanitize(string $html): string
    {
        if (trim($html) === '') return '';

        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        $root = $dom->getElementsByTagName('div')->item(0);
        if (!$root) return '';

        $out = trim(self::sanitizeNode($root));

        // A source's own indentation/line-wrapping is insignificant
        // whitespace (same as a browser treats it) — collapsed here since
        // it's preserved verbatim in text nodes above, and the images/
        // links this allowlist drops often leave behind a <p> with
        // nothing but that whitespace in it.
        $out = preg_replace('/[ \t\n\r]+/', ' ', $out);
        $out = preg_replace('/<p>\s*<\/p>/', '', $out);
        $out = preg_replace('/(?:\s*<br>\s*){3,}/', '<br><br>', $out);

        return trim($out);
    }

    private static function sanitizeNode(\DOMNode $node): string
    {
        $out = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMText) {
                $out .= htmlspecialchars($child->textContent, ENT_QUOTES, 'UTF-8');
            } elseif ($child instanceof \DOMElement) {
                $tagName = strtolower($child->tagName);
                if (isset(self::DROP_ENTIRELY[$tagName])) {
                    continue;
                }
                $inner = self::sanitizeNode($child);
                $tag = self::ALLOWED_TAGS[$tagName] ?? null;
                if ($tag === null) {
                    $out .= $inner;
                } elseif ($tag === 'br') {
                    $out .= '<br>';
                } else {
                    $out .= "<{$tag}>{$inner}</{$tag}>";
                }
            }
        }
        return $out;
    }
}
