@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sell.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="content__inner">
        <h2 class="content__title">商品の出品</h2>
        <form class="form" action="/" method="post" enctype="multipart/form-data">
            @csrf
            <div class="form__group">
                <label for="image" class="form__label">商品画像</label>
                <div class="form__image">
                    @if (!empty($item->image))
                    <img src="{{ asset('storage/item-img/' . $item->image) }}" class="form__image--img">
                    @endif
                    <input type="file" name="image" id="image" class="form__image--input">
                    <label for="image" class="form__image--label">画像を選択する</label>
                </div>
            </div>
            @error('image')
                <p class="error">{{ $message }}</p>
            @enderror
            <h3 class="form__title">商品の詳細</h3>
            <div class="form__group">
                <label class="form__label">カテゴリー</label>
                <div class="category__group">
                @foreach ($categories as $category)
                    <input type="checkbox" class="category__checkbox" id="category_id_{{ $category->id }}" name="category_id[]" value="{{ $category->id }}">
                    <label for="category_id_{{ $category->id }}" class="category__label">{{ $category->name }}</label>
                @endforeach
                </div>
                @error('category_id')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form__group">
                <label for="status_id" class="form__label">商品の状態</label>
                <select name="status_id" id="status_id" class="status__select">
                    <option value="" class="select__placeholder" disabled selected hidden>選択してください</option>
                    @foreach ($statuses as $status)
                    <option value="{{ $status->id }}" class="status__option">{{ $status->name }}</option>
                    @endforeach
                </select>
                <div class="custom-select">
                    <div class="custom-select__trigger">選択してください</div>
                    <div class="custom-options">
                        @foreach ($statuses as $status)
                        <div class="custom-option" data-value="{{ $status->id }}">{{ $status->name }}</div>
                        @endforeach
                    </div>
                </div>
                @error('status_id')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <h3 class="form__title">商品名と説明</h3>
            <div class="form__group">
                <label for="name" class="form__label">商品名</label>
                <input type="text" name="name" id="name" class="form__input" value="{{ old('name') }}">
                @error('name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form__group">
                <label for="brand" class="form__label">ブランド名</label>
                <input type="text" name="brand" id="brand" class="form__input" value="{{ old('brand') }}">
            </div>
            <div class="form__group">
                <label for="description" class="form__label">商品の説明</label>
                <textarea name="description" id="description" class="form__textarea">{{ old('description') }}</textarea>
                @error('description')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form__group">
                <label for="price" class="form__label">販売価格</label>
                <span class="price__symbol">¥</span>
                <input type="text" name="price" id="price" class="form__input--price" value="{{ old('price') }}">
                @error('price')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="form__button">出品する</button>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('status_id');
        const customSelect = document.querySelector('.custom-select');
        const trigger = customSelect.querySelector('.custom-select__trigger');
        const options = customSelect.querySelector('.custom-options');
        const customOptions = options.querySelectorAll('.custom-option');

    trigger.addEventListener('click', () => {
        options.style.display = options.style.display === 'block' ? 'none' : 'block';
    });

    customOptions.forEach(option => {
        option.addEventListener('click', () => {
            const text = option.textContent;
            const value = option.getAttribute('data-value');

        select.value = value;

        trigger.textContent = text;

        options.style.display = 'none';
        });
    });

    document.addEventListener('click', (e) => {
        if (!customSelect.contains(e.target)) {
            options.style.display = 'none';
        }
    });
});
</script>
@endsection