<?php
namespace App\Modules\Importer\Drivers;

use App\Modules\Importer\Models\ScrapedToyDTO;
use App\Modules\Importer\Support\DescriptionSanitizer;

class RebelscumDriver extends AbstractSiteDriver
{
    /**
     * Every labeled field and prose section on these pages is marked the
     * same way: <font color="#022E85">Label:</font> value — one shared
     * color used for both short spec labels (Source, Date Stamp,
     * Assortment No., Weapons and Accessories, ...) AND the longer prose
     * section headers (Point of Interest, Comments, Major Variations),
     * with genuinely inconsistent <b>/<font> nesting around it (this is
     * 2000s-era hand-written HTML). Scanning for this one marker across
     * the whole content region and slicing "everything until the next
     * marker" as each label's value is far more robust here than trying
     * to model it with clean XPath sibling navigation.
     */
    private const LABEL_COLOR_PATTERN = '/<font\s+color="#022E85"[^>]*>(.*?)<\/font>/is';

    public function getSiteName(): string
    {
        return 'Rebelscum';
    }

    public function canHandle(string $url): bool
    {
        return strpos($url, 'rebelscum.com') !== false;
    }

    public function isOverviewPage(string $url): bool
    {
        return false;
    }

    public function parseOverviewPage(string $url): array
    {
        return [];
    }

    public function parseSinglePage(string $url): ScrapedToyDTO
    {
        $html = $this->fetchUrl($url);

        if (empty($html)) {
            throw new \RuntimeException("Empty HTML returned from URL");
        }

        $base = 'https://www.rebelscum.com';

        $dto = new ScrapedToyDTO();
        $dto->externalUrl = $url;

        $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH) ?? '');
        $dto->externalId = $pathInfo['filename'] ?? md5($url);

        // The big title block reads "<Modern Name> (<variant>) /<br>
        // <Original Cardback Name> ..." — the modern name (before the
        // first line break) is what the rest of this app's naming
        // convention already matches (e.g. "Darth Vader (Bespin
        // Fatigues)"); the vintage cardback name after the "/" is
        // interesting trivia but not what should populate the toy's name.
        if (preg_match('/<font\s+face="arial"\s+size="4"\s+color="#022E85">(.*?)<br/is', $html, $m)) {
            $dto->name = trim(rtrim(trim(strip_tags($m[1])), '/'));
        }
        if ($dto->name === '') {
            $dto->name = 'Unknown Toy';
        }

        // Bound the scan to the actual article content, so a stray use of
        // this color code elsewhere on the page (nav, ads) can't get
        // picked up as a field.
        $contentHtml = $html;
        if (preg_match('/<div id="content-mainarea-pages">(.*?)<!--\s*END:\s*content-mainarea\s*-->/is', $html, $m)) {
            $contentHtml = $m[1];
        }

        // The last labeled section (usually "Major Variations") has no
        // following label to stop its value at, so it would otherwise run
        // all the way to the end of the bounded region above — which
        // always ends with a "Back to <category>" nav link sitting
        // outside any table. Every real content block on these pages IS
        // inside a <table>, so truncating at the last </table> drops that
        // trailing nav without needing to match its (category-specific)
        // wording.
        $lastTableEnd = strripos($contentHtml, '</table>');
        if ($lastTableEnd !== false) {
            $contentHtml = substr($contentHtml, 0, $lastTableEnd + strlen('</table>'));
        }

        $fields = $this->extractLabeledFields($contentHtml);

        $year = $this->fieldValueContaining($fields, 'Release Date') ?: $this->fieldValueContaining($fields, 'Date Stamp');
        if ($year !== null && preg_match('/\d{4}/', strip_tags($year), $m)) {
            $dto->year = $m[0];
        }

        $assortment = $this->fieldValueContaining($fields, 'Assortment');
        if ($assortment !== null) {
            $dto->assortmentSku = trim(strip_tags($assortment));
        }

        // No manufacturer field exists anywhere on the page in a
        // structured, labeled form (unlike Assortment No./Retail/etc.) —
        // left unset rather than guessed from context.

        $weapons = $this->fieldValueContaining($fields, 'Weapons and Accessories');
        if ($weapons !== null && preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $weapons, $liMatches)) {
            foreach ($liMatches[1] as $li) {
                $name = trim(strip_tags($li));
                if ($name !== '') {
                    $dto->items[] = $name;
                }
            }
        }

        // The genuine descriptive prose is spread across up to three
        // separate labeled sections — combine them into one description,
        // each under its own bold heading, preserving the source's own
        // bold/italic emphasis (movie titles, product names) rather than
        // flattening everything to plain text.
        $proseLabels = ['Point of Interest', 'Comments', 'Major Variations'];
        $proseParts = [];
        foreach ($proseLabels as $label) {
            $value = $this->fieldValueContaining($fields, $label);
            if ($value === null) continue;
            $safe = DescriptionSanitizer::sanitize($value);
            if ($safe !== '') {
                $proseParts[] = "<b>{$label}:</b> {$safe}";
            }
        }
        if (!empty($proseParts)) {
            $dto->description = implode('<br><br>', $proseParts);
        }

        // Every real product photo on these pages is linked the same way:
        // <a href="/photo.asp?image=/path/to/full-size.jpg"> wrapping a
        // smaller preview <img> — the query string IS the full-size image
        // path, so there's no need to follow the viewer page itself. This
        // one pattern alone covers the hero shot, both header thumbnails,
        // and every comparison/variation photo further down the page;
        // decorative site-chrome images (banners, dividers) are never
        // linked this way, so nothing else needs excluding.
        if (preg_match_all('/\/photo\.asp\?image=([^"&]+)/i', $contentHtml, $photoMatches)) {
            foreach (array_unique($photoMatches[1]) as $imagePath) {
                $dto->images[] = $this->fixRelativeUrl(html_entity_decode($imagePath), $base);
            }
        }

        return $dto;
    }

    /**
     * Splits $html into an ordered list of ['label' => cleaned label
     * text, 'value' => raw HTML from the end of this label's marker up to
     * the start of the next one] by scanning for LABEL_COLOR_PATTERN.
     */
    private function extractLabeledFields(string $html): array
    {
        if (!preg_match_all(self::LABEL_COLOR_PATTERN, $html, $matches, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        $fields = [];
        $count = count($matches[0]);
        for ($i = 0; $i < $count; $i++) {
            $rawLabel = $matches[1][$i][0];
            $label = trim(preg_replace('/(:|&nbsp;|\s)+$/i', '', trim(strip_tags($rawLabel))));
            if ($label === '') continue;

            $matchEnd = $matches[0][$i][1] + strlen($matches[0][$i][0]);
            $nextStart = $i + 1 < $count ? $matches[0][$i + 1][1] : strlen($html);
            $value = substr($html, $matchEnd, $nextStart - $matchEnd);

            $fields[] = ['label' => $label, 'value' => $value];
        }

        return $fields;
    }

    /**
     * The value of the first field whose label contains $needle
     * (case-insensitive) — labels on this site have minor inconsistent
     * whitespace/punctuation (e.g. "Assortment No. :"), so a fuzzy
     * contains-match is more robust than an exact key lookup.
     */
    private function fieldValueContaining(array $fields, string $needle): ?string
    {
        foreach ($fields as $field) {
            if (stripos($field['label'], $needle) !== false) {
                return $field['value'];
            }
        }
        return null;
    }
}
