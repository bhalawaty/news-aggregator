<?php
namespace App\Services\NewsProviders;

use Carbon\Carbon;

interface ProviderInterface {

    /**
     * @param int $sourceId
     * @param Carbon|null $since
     * @param int $pageSize
     * @return array
     */
    public function fetchNew(int $sourceId, ?Carbon $since = null, int $pageSize = 100): array;
}
