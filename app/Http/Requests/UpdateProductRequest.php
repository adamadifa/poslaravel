<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        $productId = $this->route('product') ? $this->route('product')->id : $this->input('product_id');

        return [
            'name' => ['required', 'string', 'max:200'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('products', 'code')->ignore($productId)],
            'barcode' => ['nullable', 'string', 'max:50', Rule::unique('products', 'barcode')->ignore($productId)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'base_unit_id' => ['required', 'exists:units,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'max_stock' => ['nullable', 'numeric', 'min:0'],
            'brand' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'tax_type' => ['nullable', 'in:none,inclusive,exclusive'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'has_expiry' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],

            // Multi-Barcode array
            'barcodes' => ['nullable', 'array'],
            'barcodes.*.barcode' => ['required_with:barcodes', 'string', 'max:50'],
            'barcodes.*.unit_id' => ['required_with:barcodes', 'exists:units,id'],

            // Unit Conversions array
            'conversions' => ['nullable', 'array'],
            'conversions.*.from_unit_id' => ['required_with:conversions', 'exists:units,id'],
            'conversions.*.to_unit_id' => ['required_with:conversions', 'exists:units,id'],
            'conversions.*.conversion_value' => ['required_with:conversions', 'numeric', 'gt:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk wajib diisi.',
            'name.max' => 'Nama produk maksimal 200 karakter.',
            'code.unique' => 'Kode SKU produk sudah digunakan.',
            'barcode.unique' => 'Barcode utama sudah digunakan produk lain.',
            'base_unit_id.required' => 'Satuan dasar wajib dipilih.',
            'base_unit_id.exists' => 'Satuan dasar tidak valid.',
            'purchase_price.required' => 'Harga beli (HPP) wajib diisi.',
            'purchase_price.numeric' => 'Harga beli harus berupa angka.',
            'purchase_price.min' => 'Harga beli tidak boleh negatif.',
            'selling_price.required' => 'Harga jual wajib diisi.',
            'selling_price.numeric' => 'Harga jual harus berupa angka.',
            'selling_price.min' => 'Harga jual tidak boleh negatif.',
            'image.image' => 'File harus berupa gambar (JPG, PNG, WEBP).',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}
