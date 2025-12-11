<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Article extends Model
{
    use Searchable;
    use HasFactory;

    protected $fillable = ['source_id', 'external_id','category_id', 'url', 'canonical_url','author',
        'title', 'summary', 'content', 'published_at', 'language', 'image_url', 'normalized_hash', 'ingestion_metadata'];
    protected $casts = ['ingestion_metadata' => 'array', 'published_at' => 'datetime'];

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'summary' => $this->summary,
            'published_at' => $this->published_at?->toIso8601String(),
            'source' => $this->source->name ?? null,
            'category' => $this->category->name ?? null
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
