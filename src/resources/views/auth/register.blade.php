@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="content__inner">
        <h2 class="content__title">会員登録</h2>
        <form class="form" action="/register" method="post">
            @csrf
            <div class="form__group">
                <label for="name" class="form__label">ユーザー名</label>
                <input type="text" name="name" id="name" class="form__input" value="{{ old('name') }}">
                @error('name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form__group">
                <label for="email" class="form__label">メールアドレス</label>
                <input type="email" name="email" id="email" class="form__input" value="{{ old('email') }}">
                @error('email')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form__group">
                <label for="password" class="form__label">パスワード</label>
                <input type="password" name="password" id="password" class="form__input">
                @error('password')
                    @if (!str_contains($message, '一致'))
                        <p class="error">{{ $message }}</p>
                    @endif
                @enderror
            </div>
            <div class="form__group">
                <label for="password_confirmation" class="form__label">確認用パスワード</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form__input">
                @error('password')
                    @if (str_contains($message, '一致'))
                        <p class="error">{{ $message }}</p>
                    @endif
                @enderror
            </div>
            <button type="submit" class="form__button">登録する</button>
        </form>
        <div class="link">
            <a href="/login" class="link__action">ログインはこちら</a>
        </div>
    </div>
</div>
@endsection