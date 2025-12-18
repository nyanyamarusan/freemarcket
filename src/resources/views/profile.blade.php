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
            <div class="user__info">
                <p class="user__name">{{ $user->name }}</p>
                @if ($user->average_rating !== null)
                <div class="rating" data-rating="{{ $user->average_rating }}">
                    @for ($i = 1; $i <= 5; $i++)
                    <span class="star {{ $i <= $user->average_rating ? 'is-active' : '' }}">
                        ★
                    </span>
                    @endfor
                </div>
                @endif
            </div>

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
            <div class="transaction">
                <a href="/mypage?page=transaction" class="{{ $tab === 'transaction' ? 'active-tab' : 'inactive-tab' }}">
                    取引中の商品
                </a>
                @if ($totalCount > 0)
                <span class="total-count">{{ $totalCount }}</span>
                @endif
            </div>
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
        @if ($tab === 'transaction')
            @foreach ($transactions as $transaction)
            <a href="/transaction/{{ $transaction->id }}" class="item-card">
                <div class="item-image">
                    <img class="item-image__img" src="{{ asset('storage/item-img/' . $transaction->item->image) }}">
                    @isset ($count[$transaction->id])
                        <span class="count">{{ $count[$transaction->id] }}</span>
                    @endisset
                </div>
            </a>
            @endforeach
        @endif
    </div>
</div>
@endsection