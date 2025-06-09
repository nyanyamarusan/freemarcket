@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection

@section('content')
<div class="content">
    <p class="text">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>
    <a href="http://localhost:8025/" target="_blank" class="btn">認証はこちらから</a>
    <form action="{{ route('verification.send') }}" method="post">
        @csrf
        <button type="submit" class="link">認証メールを再送する</button>
    </form>
</div>
@endsection