<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ArticlePreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sources' => 'nullable|array',
            'sources.*' => 'integer|exists:sources,id',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
            'authors' => 'nullable|array|min:1',
            'authors.*' => 'string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
            'search' => 'nullable|string|max:255',
        ];
    }


    public function preferences(): array
    {
        return [
            'sources' => $this->input('sources', []),
            'categories' => $this->input('categories', []),
            'authors' => $this->input('authors', []),
        ];
    }

    public function perPage(): int
    {
        return (int) ($this->input('per_page', 15));
    }
}
