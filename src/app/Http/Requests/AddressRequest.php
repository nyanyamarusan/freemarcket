<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
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
        return [
            'shipping_zipcode' => 'required|regex:/^(?=.*\-).{8}$/',
            'shipping_address' => 'required|string',
            'shipping_building' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_zipcode.required' => '郵便番号を入力してください',
            'shipping_zipcode.regex' => '郵便番号はハイフンありの8文字で入力してください',
            'shipping_address.required' => '住所を入力してください',
            'shipping_building.required' => '建物名を入力してください',
        ];
    }
}
