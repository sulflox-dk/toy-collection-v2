<?php
namespace App\Modules\Importer\Drivers;

use DOMDocument;
use DOMXPath;

abstract class AbstractSiteDriver implements SiteDriverInterface
{
    protected function fetchUrl(string $url, int $timeout = 15): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        );

        $result = curl_exec($ch);
        $error = curl_error($ch);

        if ($result === false) {
            throw new \RuntimeException("Failed to fetch URL: $error");
        }

        return $result;
    }

    protected function createXPath(string $html): DOMXPath
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        return new DOMXPath($dom);
    }

    protected function getText(DOMXPath $xpath, string $query): string
    {
        $nodes = $xpath->query($query);
        if ($nodes && $nodes->length > 0) {
            return trim($nodes->item(0)->textContent);
        }
        return '';
    }

    /**
     * Resolve a scraped src/href against the page it came from. $baseUrl is
     * usually just a bare "https://site.com" (root-relative paths are the
     * common case and don't need more), but a document-relative path like
     * "../img/x.png" needs the base's own path to resolve against — pass
     * the actual page URL when a site uses those, not just its domain.
     */
    protected function fixRelativeUrl(string $src, string $baseUrl): string
    {
        if (strpos($src, 'http') === 0) {
            return $src;
        }

        $baseParts = parse_url($baseUrl);
        $scheme = $baseParts['scheme'] ?? 'https';
        $host = $baseParts['host'] ?? '';
        if (!empty($baseParts['port'])) {
            $host .= ':' . $baseParts['port'];
        }
        $basePath = $baseParts['path'] ?? '/';

        if (strpos($src, '/') === 0) {
            $path = $src;
        } else {
            $path = rtrim(dirname($basePath), '/') . '/' . $src;
        }

        // Collapse "." and ".." segments the way a browser would.
        $segments = [];
        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($segments);
                continue;
            }
            $segments[] = $segment;
        }

        return "{$scheme}://{$host}/" . implode('/', $segments);
    }
}
