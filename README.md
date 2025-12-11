# News Aggregator System Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [API Endpoints](#api-endpoints)
3. [Architecture](#architecture)
4. [Scheduled Tasks](#scheduled-tasks)
5. [Adding New News Providers](#adding-new-news-providers)
6. [Database Models](#database-models)

---

## System Overview

The News Aggregator is a robust Laravel-based system that automatically fetches, normalizes, deduplicates, and serves news articles from multiple sources via REST APIs. The system runs scheduled jobs every 5 minutes to keep content fresh and prevents duplicate articles through intelligent hashing and comparison.

### Key Features
- Multi-source news fetching with extensible provider system
- Automatic duplicate detection using normalized hashes
- Full-text search capability via Laravel Scout
- User preference-based article filtering
- Paginated API responses
- Rate limiting and error handling
- Incremental fetching (only new articles since last fetch)
- Bulk upsert operations for performance

---

## API Endpoints

### 1. Get All Articles (with filters)

**Endpoint:** `GET /api/v1/articles`

**Description:** Retrieve paginated articles with optional filtering and search

**Query Parameters:**
```
- search (string): Full-text search across title, content, summary, source, category
- source_id (integer): Filter by source ID
- category_id (integer): Filter by category ID
- author (string): Filter by author name (case-insensitive partial match)
- from_date (date): Filter articles published on or after this date
- to_date (date): Filter articles published on or before this date
- per_page (integer): Items per page (default: 15)
- page (integer): Page number for pagination
```

**Example Request:**
```
GET /api/v1/articles?source_id=1&category_id=2&from_date=2025-01-01&per_page=20&page=1
```

**HTTP Status Codes:**
- 200: Success
- 400: Invalid parameters

---

### 2. Get Single Article

**Endpoint:** `GET /api/v1/articles/{id}`

**Description:** Retrieve a single article by ID with all relationships

**Path Parameters:**
```
- id (integer): Article ID
```

**Example Request:**
```
GET /api/v1/articles/42
```

**HTTP Status Codes:**
- 200: Success
- 404: Article not found

---

### 3. Get Articles by User Preferences

**Endpoint:** `POST /api/v1/articles/preferences`

**Description:** Retrieve articles filtered by user preferences (sources, categories, authors)

**Request Body:**
```json
{
  "sources": [1, 2, 3],
  "categories": [1, 2],
  "authors": ["John Doe", "Jane Smith"],
  "per_page": 20
}
```

**Query Parameters:**
```
- per_page (integer): Items per page (default: 15)
- page (integer): Page number for pagination
```

**Example Request:**
```
POST /api/v1/articles/preferences
Content-Type: application/json

{
  "sources": [1, 2],
  "categories": [1],
  "authors": ["Reuters Staff"],
  "per_page": 25
}
```

**HTTP Status Codes:**
- 200: Success
- 400: Invalid preferences format
- 422: Validation error

---

## Architecture

### High-Level System Flow

```
Scheduled Task (Every 5 minutes)
    ↓
Dispatcher → FetchSourceJob (for each enabled source)
    ↓
NewsFetchService → ProviderFactory
    ↓
NewsProvider (Guardian, NewsAPI, etc.)
    ↓
HTTP Request → External News API
    ↓
NewsArticleDto (normalize data)
    ↓
ProcessArticleChunkJob (chunk by 100)
    ↓
ArticleService → bulkUpsert
    ↓
Database (detect duplicates, upsert/update)
    ↓
Search Index (Scout/Elasticsearch)
```
---

## Scheduled Tasks

### Command: `php artisan news:fetch`

**Description:** Manually trigger news fetching (mainly for testing)

**Usage:**

Fetch from a specific source:
```
php artisan news:fetch guardian
```

Fetch from all enabled sources:
```
php artisan news:fetch
```

**How it works:**
1. Validates source exists and is enabled
2. Dispatches FetchSourceJob to queue
3. Job handles provider initialization and article processing

---

## Duplicate Detection Strategy

### How It Works

1. **Prepare Lookup Data**: For each article, generate:
    - Canonical URL (remove tracking params)
    - Normalized hash: SHA-256(url + title + first-500-chars-of-content)

2. **Batch Query**: Fetch all existing articles matching these hashes/URLs

3. **Filter**: Remove articles that already exist in database

4. **Bulk Upsert**: Use `updateOrCreate` to insert new or update existing

### Why It's Effective

- Same article published across sources → detected by content hash
- Duplicate from same source with slight URL change → caught by title+content
- Tracking parameter differences (utm_*) → normalized away

---

## Error Handling & Monitoring

### Logging

All operations logged to `storage/logs/laravel.log`:

```
[2025-01-15 10:30:45] local.INFO: Fetched articles from guardian [count: 25, source: "The Guardian"]
[2025-01-15 10:30:50] local.DEBUG: Filtered duplicates from guardian [total: 25, duplicates: 3, unique: 22]
[2025-01-15 10:35:22] local.ERROR: Failed to fetch from newsapi: Invalid API key
[2025-01-15 10:35:22] local.CRITICAL: Permanently failed fetching source 2: Invalid API key
```

### Job Retry Logic

FetchSourceJob configuration:
- **Tries**: 3 attempts
- **Backoff**: 10s, 30s, 60s between retries
- **Timeout**: 120 seconds max execution
- **Failed**: Logged as CRITICAL after all retries exhausted

### Rate Limiting

Per-provider enforcement prevents API throttling:
```php
Rate limit exceeded for guardian. Retry in 3542s
```

---
