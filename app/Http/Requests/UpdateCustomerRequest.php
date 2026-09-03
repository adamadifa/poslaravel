<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
        $customerId = $this->route('customer') ? $this->route('customer')->id : $this->input('customer_id');

        return [
            'name' => ['required', 'string', 'max:200'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('customers', 'code')->ignore($customerId)],
            'customer_group_id' => ['required', 'exists:customer_groups,id'],
            'phone' => ['nullable', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
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
            'name.required' => 'Nama pelanggan wajib diisi.',
            'customer_group_id.required' => 'Grup / kategori member wajib dipilih.',
            'customer_group_id.exists' => 'Grup member yang dipilih tidak valid.',
            'code.unique' => 'Kode pelanggan sudah digunakan.',
            'email.email' => 'Format email tidak valid.',
            'credit_limit.numeric' => 'Batas piutang / limit kredit harus berupa angka.',
            'credit_limit.min' => 'Batas piutang tidak boleh bernilai negatif.',
        ];
    }
}
