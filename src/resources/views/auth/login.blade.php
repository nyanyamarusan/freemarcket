@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="content__inner">
        <h2 class="content__title">ログイン</h2>
        <form class="form"action="/login" method="post">
            @csrf
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
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="form__button">ログインする</button>
        </form>
        <div class="link">
            <a href="/register" class="link__action">会員登録はこちら</a>
        </div>
    </div>
</div>
@endsection