<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
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
            'description' => 'required|string|max:255',
            'image' => 'required|mimes:jpeg,png',
            'category_id' => 'required',
            'status_id' => 'required',
            'price' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '商品名を入力してください',
            'description.required' => '商品説明を入力してください',
            'description.max' => '商品説明は255文字以内で入力してください',
            'image.mimes' => '画像はjpegまたはpng形式でアップロードしてください',
            'image.required' => '画像をアップロードしてください',
            'category_id.required' => 'カテゴリーを選択してください',
            'status_id.required' => '商品の状態を選択してください',
            'price.required' => '価格を入力してください',
            'price.integer' => '価格は整数で入力してください',
            'price.min' => '価格は0円以上で入力してください',
        ];
    }
}
