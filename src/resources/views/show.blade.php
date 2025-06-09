@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/show.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="content__inner">
        <div class="item-image">
            <img class="item-image__img" src="{{ asset('storage/item-img/' . $item->image) }}">
            @if ($item->sold)
                <span class="sold-label">Sold</span>
            @endif
        </div>
        <div class="item-info">
            <div class="item-info__group">
                <h2 class="item-name">{{ $item->name }}</h2>
                <p class="item-brand">{{ $item->brand }}</p>
                <span class="yen">¥</span>
                <p class="item-price">{{ number_format($item->price) }}</p>
                <span class="tax">(税込)</span>
                <div class="action">
                    <div class="like">
                        <form action="/item/{{ $item->id }}" method="post" class="like-form">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="like__icon
                            {{ auth()->user() && auth()->user()->likes?->contains($item->id) ? 'liked' : '' }}">
                                <img src="{{ asset('icon/like.png') }}" class="icon__img">
                                <span class="icon__text">★</span>
                            </button>
                        </form>
                        <span class="count">
                        {{ $item->likes_count }}
                        </span>
                    </div>
                    <div class="comment">
                        <div class="comment__icon">
                            <img src="{{ asset('icon/comment.png') }}" class="icon__img">
                        </div>
                        <span class="count">
                            {{ $item->comments_count }}
                        </span>
                    </div>
                </div>
                <div class="buy-area">
                    @if (!$item->sold)
                    <a href="/purchase/{{ $item->id }}" class="buy-button">購入手続きへ</a>
                    @else
                    <p class="sold-message">売り切れました</p>
                    @endif
                </div>
            </div>
            <div class="item-info__group">
                <h3 class="item-description">商品説明</h3>
                <p class="item-description__text">{{ $item->description }}</p>
            </div>
            <div class="item-info__group">
                <h3 class="item-info__info">商品の情報</h3>
                <div class="item-info__table">
                    <table class="item-info__table--main">
                        <tr>
                            <th class="item-info__table--th">カテゴリー</th>
                            <td class="item-info__table--td">
                                <ul class="category-list">
                                    @foreach ($categories as $category)
                                        <li class="category-item">{{ $category->name }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <th class="item-info__table--th">商品の状態</th>
                            <td class="item-info__table--td">
                                <p class="status">{{ $item['status']['name']}}</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="item-info__group">
                <h3 class="item-info__comment">コメント({{ $item->comments_count }})</h3>
                <div class="comment-list">
                    @foreach ($comments as $comment)
                        <div class="comment__user">
                            <img src="{{ asset('storage/user-img/' . $comment->user->image) }}" class="user__icon">
                            <p class="user__name">{{ $comment->user->name }}</p>
                        </div>
                        <p class="comment__text">{{ $comment->content }}</p>
                    @endforeach
                </div>
                <form action="/item/{{ $item->id }}" method="post" class="comment-form">
                    @csrf
                    <label for="content" class="comment-form__label">商品へのコメント</label>
                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                    <textarea name="content" id="content" class="comment-form__textarea"></textarea>
                    @error('content')
                        <p class="error">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="comment-form__button">コメントを送信する</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection