@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/transaction.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="sidebar">
        <p class="transaction-list__title">その他の取引</p>
        <ul class="transaction-list">
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
        <div class="title-content">
            <div class="title">
                @if ($partner->image)
                <img src="{{ asset('storage/profile-img/' . $partner->image) }}" class="user__icon">
                @else
                <span class="user__icon--none"></span>
                @endif
                <h2 class="user__name">「{{ $partner->name }}」さんとの取引画面</h2>
            </div>
            <form action="/transaction/{{ $selectedTransaction->id }}/completed" method="post" class="completed-form">
                @csrf
                @method('PATCH')
                <div class="button">
                    <button class="button__completed" type="submit">取引を完了する</button>
                </div>
            </form>
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
            @if ($isMe)
            <div class="message--me">
                <div class="message">
                    <div class="message__user--me">
                        <p class="message__user-name">{{ $message->user->name }}</p>
                        @if ($message->user->image)
                        <div class="message__user-icon">
                            <img src="{{ asset('storage/profile-img/' . $message->user->image) }}" class="message__user-icon--img">
                        </div>
                        @else
                        <span class="message__user-icon--none"></span>
                        @endif
                    </div>
                    @if ($message->image)
                    <div class="message__image-container">
                        <div class="message__image">
                            <img src="{{ asset('storage/message-img/' . $message->image) }}" class="message__image--img">
                        </div>
                    </div>
                    @endif
                    <form action="/message/{{ $message->id }}" method="post" class="message-update-form">
                        @csrf
                        @method('PATCH')
                        <textarea name="message" class="message-update-form__textarea">{{ $message->message }}</textarea>
                        <div class="form-button">
                            <button type="submit" class="message-form__button">編集</button>
                        </div>
                    </form>
                    <form action="/message/{{ $message->id }}" method="post" class="message-delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="message-form__button">削除</button>
                    </form>
                </div>
            </div>
            @else
            <div class="message--partner">
                <div class="message">
                    <div class="message__user">
                        @if ($message->user->image)
                        <div class="message__user-icon">
                            <img src="{{ asset('storage/profile-img/' . $message->user->image) }}" class="message__user-icon--img">
                        </div>
                        @else
                        <span class="message__user-icon--none"></span>
                        @endif
                        <p class="message__user-name">{{ $message->user->name }}</p>
                    </div>
                    @if ($message->image)
                    <div class="message__image">
                        <img src="{{ asset('storage/message-img/' . $message->image) }}" class="message__image--img">
                    </div>
                    @endif
                    <p class="message__text">{{ $message->message }}</p>
                </div>
            </div>
            @endif
            @endforeach
        </div>
        <div class="message-form-container">
            <form action="/transaction/{{ $selectedTransaction->id }}/message" method="post" enctype="multipart/form-data">
                @csrf
                @error('message')
                <p class="error">{{ $message }}</p>
                @enderror
                @error('image')
                <p class="error">{{ $message }}</p>
                @enderror
                <div class="message-form">
                    <textarea name="message" class="message-form__textarea" placeholder="取引メッセージを記入してください">{{ old('message', $sessionMessage) }}</textarea>
                    <input type="file" name="image" id="image" class="message-form__file">
                    <label for="image" class="message-form__label">画像を追加</label>
                    <button type="submit" class="message-form__send-button">
                        <img src="{{ asset('icon/send.jpg') }}" class="send-icon">
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection