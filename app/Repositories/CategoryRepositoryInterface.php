<?php

namespace App\Repositories;


interface CategoryRepositoryInterface
{

    /**
     * @param array $names
     * @return array
     */
    public function bulkFindOrCreate(array  $names): array;

}
