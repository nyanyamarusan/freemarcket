<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
    public function prepareForValidation()
    {
        $this->merge([
            'shipping_address' => trim(
                $this->shipping_zipcode . $this->shipping_address . $this->shipping_building
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'payment_method_id' => 'required',
            'shipping_address' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method_id.required' => '支払い方法を選択してください',
            'shipping_address.required' => '配送先を選択してください',
        ];
    }
}
