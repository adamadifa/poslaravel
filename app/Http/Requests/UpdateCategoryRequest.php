<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')?->id ?? $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')
                    ->ignore($categoryId)
                    ->where(fn ($query) => $query->where('parent_id', $this->parent_id)),
            ],
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                Rule::notIn([$categoryId]), // Cannot set itself as parent
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom error messages for validation.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi',
            'name.string' => 'Nama kategori harus berupa teks yang valid',
            'name.max' => 'Nama kategori maksimal 100 karakter',
            'name.unique' => 'Nama kategori sudah ada pada tingkat/induk yang sama',
            'parent_id.exists' => 'Induk kategori yang dipilih tidak valid',
            'parent_id.not_in' => 'Kategori tidak dapat menjadi induk bagi dirinya sendiri',
            'description.max' => 'Deskripsi maksimal 500 karakter',
        ];
    }
}
