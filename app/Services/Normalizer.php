<?php

namespace App\Services;


class Normalizer
{

    /**
     * @param string|null $url
     * @return string|null
     */
    public static function canonicalizeUrl(?string $url): ?string
    {
        if (!$url) {return null;}

        $url = trim($url);
        $parts = parse_url($url);

        if (!$parts) {
            return $url;
        }

        $query = '';
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $qs);
            foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as $param) {
                unset($qs[$param]);
            }
            $query = http_build_query($qs);
        }

        $scheme = $parts['scheme'] ?? 'http';
        $host = $parts['host'] ?? '';
        $path = $parts['path'] ?? '';

        $canonical = "{$scheme}://{$host}{$path}";
        if ($query) {
            $canonical .= "?{$query}";
        }

        return rtrim($canonical, '/');
    }

    /**
     * @param string|null $url
     * @param string|null $title
     * @param string|null $content
     * @return string
     */
    public static function normalizedHash(?string $url = null, ?string $title = null, ?string $content = null): string
    {
        $contentSnippet = substr($content ?? '', 0, 500);

        $stringToHash = ($url ?? '') . '|' . ($title ?? '') . '|' . $contentSnippet;

        return hash('sha256', $stringToHash);
    }
}
