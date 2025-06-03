@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/address.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="content__inner">
        <h2 class="content__title">住所の変更</h2>
        <form class="form" action="/purchase/{{ $item->id }}" method="post">
            @csrf
            @method('PATCH')
            <div class="form__group">
                <label for="shipping_zipcode" class="form__label">郵便番号</label>
                <input type="text" name="shipping_zipcode" id="shipping_zipcode" class="form__input">
                @error('shipping_zipcode')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form__group">
                <label for="shipping_address" class="form__label">住所</label>
                <input type="text" name="shipping_address" id="shipping_address" class="form__input">
                @error('shipping_address')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form__group">
                <label for="shipping_building" class="form__label">建物名</label>
                <input type="text" name="shipping_building" id="shipping_building" class="form__input">
                @error('shipping_building')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="form__button">更新する</button>
        </form>
    </div>
</div>
@endsection