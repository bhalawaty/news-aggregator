<?php

namespace App\Dto;

class NewsArticleDto
{
    public function __construct(
        public ?string $externalId,
        public ?string $url,
        public ?string $canonicalUrl,
        public string  $title,
        public ?string $summary,
        public ?string $content,
        public ?string $publishedAt,
        public ?string $author,
        public ?string $imageUrl,
        public ?string $language,
        public ?string $category,
        public array   $raw
    )
    {
    }
}
