@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/edit.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="content__inner">
        <h2 class="content__title">プロフィール設定</h2>
        <form class="form" action="/mypage" method="post" enctype="multipart/form-data">
            @csrf
            @method('patch')
            <div class="form__group--image">
                <div class="form__image">
                    @if ($user->image)
                    <img src="{{ asset('storage/profile-img/' . $user->image) }}" class="form__image--img">
                    @else
                    <span class="form__image--none"></span>
                    @endif
                </div>
                <input type="file" name="image" id="image" class="form__image--input">
                <label for="image" class="form__image--label">画像を選択する</label>
            </div>
            @error('image')
                <p class="error">{{ $message }}</p>
            @enderror
            <div class="form__group">
                <label for="name" class="form__label">ユーザー名</label>
                <input type="text" name="name" id="name" class="form__input" value="{{ old('name', $user->name) }}">
                @error('name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form__group">
                <label for="zipcode" class="form__label">郵便番号</label>
                <input type="text" name="zipcode" id="zipcode" class="form__input" value="{{ old('zipcode', $user->zipcode) }}">
                @error('zipcode')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form__group">
                <label for="address" class="form__label">住所</label>
                <input type="text" name="address" id="address" class="form__input" value="{{ old('address', $user->address) }}">
                @error('address')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form__group">
                <label for="building" class="form__label">建物名</label>
                <input type="text" name="building" id="building" class="form__input" value="{{ old('building', $user->building) }}">
                @error('building')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="form__button">更新する</button>
        </form>
    </div>
</div>
@endsection