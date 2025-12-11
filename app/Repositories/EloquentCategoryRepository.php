<?php


namespace App\Repositories;

use App\Models\Category;
use Illuminate\Support\Str;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{

    /**
     * @param array $names
     * @return array
     */
    public function bulkFindOrCreate(array $names): array
    {
        if (empty($names)) {
            return [];
        }

        $existing = Category::whereIn('name', $names)->get();

        $map = [];
        foreach ($existing as $cat) {
            $map[$cat->name] = $cat->id;
        }

        $toInsert = array_diff($names, array_keys($map));

        if (!empty($toInsert)) {
            $insertData = [];
            foreach ($toInsert as $name) {
                $insertData[] = [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            Category::insert($insertData);

            $new = Category::whereIn('name', $toInsert)->get();

            foreach ($new as $cat) {
                $map[$cat->name] = $cat->id;
            }
        }

        return $map;
    }
}
