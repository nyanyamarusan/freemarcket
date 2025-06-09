@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
<div class="content__head">
    <div class="profile">
        <div class="profile__inner">
            <div class="profile__image">
                @if ($user->image)
                <img src="{{ asset('storage/profile-img/' . $user->image) }}" class="profile__image--img">
                @else
                <span class="profile__image--none"></span>
                @endif
            </div>
            <p class="user__name">{{ $user->name }}</p>
            <a href="/mypage/profile" class="profile__link">プロフィールを編集</a>
        </div>
    </div>
    <div class="tab">
        <div class="tab__inner">
            <a href="/mypage?page=sell" class="{{ $tab === 'sell' ? 'active-tab' : 'inactive-tab' }}">
                出品した商品
            </a>
            <a href="/mypage?page=buy" class="{{ $tab === 'buy' ? 'active-tab' : 'inactive-tab' }}">
                購入した商品
            </a>
        </div>
    </div>
</div>
<div class="content">
    <div class="item-list">
        @if ($tab === 'sell')
        @foreach ($soldItems as $item)
        <a href="/item/{{ $item->id }}" class="item-card">
            <div class="item-image">
                <img class="item-image__img" src="{{ asset('storage/item-img/' . $item->image) }}">
                @if ($item->sold)
                    <span class="sold-label">Sold</span>
                @endif
            </div>
            <p class="item-name">{{ $item->name }}</p>
        </a>
        @endforeach
        @endif
        @if ($tab === 'buy')
            @foreach ($purchasedItems as $item)
            <a href="/item/{{ $item->id }}" class="item-card">
                <div class="item-image">
                    <img class="item-image__img" src="{{ asset('storage/item-img/' . $item->image) }}">
                    @if ($item->sold)
                        <span class="sold-label">Sold</span>
                    @endif
                </div>
                <p class="item-name">{{ $item->name }}</p>
            </a>
            @endforeach
        @endif
    </div>
</div>
@endsection