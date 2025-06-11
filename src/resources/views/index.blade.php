@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="content__head">
    <div class="tab">
        <div class="tab__inner">
            <a href="/" class="{{ $tab === 'recommend' ? 'active-tab' : 'inactive-tab' }}">
                おすすめ
            </a>
            <a href="/?page=mylist&keyword={{ $keyword }}" class="{{ $tab === 'mylist' ? 'active-tab' : 'inactive-tab' }}">
                マイリスト
            </a>
        </div>
    </div>
</div>
<div class="content">
    <div class="item-list">
        @foreach ($items as $item)
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
    </div>
</div>
@endsection
