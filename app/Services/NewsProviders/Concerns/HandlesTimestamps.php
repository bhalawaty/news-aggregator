<?php

namespace App\Services\NewsProviders\Concerns;

use App\Models\Source;
use Carbon\Carbon;


trait HandlesTimestamps
{

    /**
     * @param int $sourceId
     * @return Carbon
     */
    protected function getLastFetchTime(int $sourceId): Carbon
    {
        $source = Source::query()->find($sourceId);

        if ($source?->last_success_at) {
            return Carbon::parse($source->last_success_at);
        }

        return Carbon::now()->subHours(72);
    }



}
