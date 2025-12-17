@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/transaction.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="sidebar">
        <p class="transaction-list__title">その他の取引</p>
        <ul>
            @foreach ($transactions as $transaction)
            @if ($transaction->id !== $selectedTransaction->id)
            <li class="transaction-list__item">
                <a href="/transaction/{{ $transaction->id }}" class="transaction-list__link">
                    {{ $transaction->item->name }}
                </a>
            </li>
            @endif
            @endforeach
        </ul>
    </div>
    <div class="main">
        <div class="title__container">
            <div class="title">
                <div class="message__user">
                    @if ($partner->image)
                    <img src="{{ asset('storage/profile-img/' . $partner->image) }}" class="user__icon">
                    @else
                    <span class="user__icon--none"></span>
                    @endif
                    <h2 class="user__name">「{{ $partner->name }}」さんとの取引画面</h2>
                </div>
                <div class="button">
                    <button class="button__completed">取引を完了する</button>
                </div>
            </div>
            <div class="item">
                <div class="item__image">
                    <img src="{{ asset('storage/item-img/' . $selectedTransaction->item->image) }}" class="item__image--img">
                </div>
                <div class="item__info">
                    <p class="item__name">{{ $selectedTransaction->item->name }}</p>
                    <span class="yen">¥</span>
                    <p class="item__price">{{ number_format($selectedTransaction->item->price) }}</p>
                </div>
            </div>
            <div class="message-list">
                @foreach ($messages as $message)
                @php
                $isMe = $message->user_id === auth()->user()->id;
                @endphp
                <div class="message {{ $isMe ? 'message--me' : 'message--partner' }}">
                    <div class="message__user">
                        @if ($message->user->image)
                        <img src="{{ asset('storage/profile-img/' . $message->user->image) }}" class="user__icon">
                        @else
                        <span class="user__icon--none"></span>
                        @endif
                        <p class="user__name">{{ $message->user->name }}</p>
                    </div>
                    <p class="message__text">{{ $message->message }}</p>
                    @if ($message->image)
                    <div class="message__image">
                        <img src="{{ asset('storage/message-img/' . $message->image) }}" class="message__image--img">
                    </div>
                    @endif
                    @if ($isMe)
                        <div class="form">
                            <form action="/message/{{ $message->id }}" method="post" class="message-form">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="message-form__button">編集</button>
                            </form>
                            <form action="/message/{{ $message->id }}" method="post" class="message-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="message-form__button">削除</button>
                            </form>
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
            <form action="/transaction/{{ $selectedTransaction->id }}/message" method="post" class="message-form" enctype="multipart/form-data">
                @csrf
                <input type="text" name="message" id="message" class="message-form__input">
                @error('message')
                <p class="error">{{ $message }}</p>
                @enderror
                <input type="file" name="image" id="image" class="message-form__file">
                <label for="image" class="form__image--label">画像を追加する</label>
                @error('image')
                <p class="error">{{ $message }}</p>
                @enderror
                <button type="submit" class="message-form__button">送信</button>
            </form>
        </div>
    </div>
</div>

@endsection