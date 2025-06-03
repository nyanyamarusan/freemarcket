<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'name' => 'required|string',
            'zipcode' => 'required|regex:/^(?=.*\-).{8}$/',
            'address' => 'required|string',
            'building' => 'required|string',
            'image' => 'mimes:jpeg,png',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'お名前を入力してください',
            'zipcode.required' => '郵便番号を入力してください',
            'zipcode.regex' => '郵便番号はハイフンありの8文字で入力してください',
            'address.required' => '住所を入力してください',
            'building.required' => '建物名を入力してください',
            'image.mimes' => '画像はjpegまたはpng形式でアップロードしてください',
        ];
    }
}
