<?php


namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ArticleIndexRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'source' => 'nullable|integer|exists:sources,id',
            'category' => 'nullable|integer|exists:categories,id',
            'author' => 'nullable|string|max:255',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'source.exists' => 'The selected source does not exist.',
            'category.exists' => 'The selected category does not exist.',
            'to.after_or_equal' => 'The end date must be after or equal to the start date.',
            'per_page.max' => 'Maximum 100 articles per page allowed.',
        ];
    }

    public function attributes(): array
    {
        return [
            'from' => 'start date',
            'to' => 'end date',
            'per_page' => 'items per page',
        ];
    }

    public function filters(): array
    {
        return [
            'search' => $this->input('search'),
            'source_id' => $this->input('source'),
            'category_id' => $this->input('category'),
            'author' => $this->input('author'),
            'from_date' => $this->input('from'),
            'to_date' => $this->input('to'),
        ];
    }

    public function perPage(): int
    {
        return $this->input('per_page', 15);
    }
}
