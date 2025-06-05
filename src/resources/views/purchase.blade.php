@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('content')
<div class="content">
    <form action="/purchase/{{ $item->id }}" method="post" class="purchase">
        @csrf
        <div class="purchase__left">
            <div class="purchase__item">
                <div class="item-image">
                    <img src="{{ asset('storage/item-img/' . $item->image) }}" class="item-image__img">
                </div>
                <div class="item-info">
                    <h2 class="item-name">{{ $item->name }}</h2>
                    <span class="yen">¥</span>
                    <p class="item-price">{{ number_format($item->price) }}</p>
                </div>
            </div>
            <div class="payment">
                <label for="payment_method_id" class="payment__label">支払い方法</label>
                <select name="payment_method_id" id="payment_method_id" class="payment__select">
                    <option value="" class="select__placeholder" disabled selected hidden>選択してください</option>
                    @foreach ($paymentMethods as $paymentMethod)
                    <option value="{{ $paymentMethod->id}}" class="payment__option">{{ $paymentMethod->name }}</option>
                    @endforeach
                </select>
                <div class="custom-select">
                    <div class="custom-select__trigger">選択してください</div>
                    <div class="custom-options">
                        @foreach ($paymentMethods as $paymentMethod)
                        <div class="custom-option" data-value="{{ $paymentMethod->id }}">{{ $paymentMethod->name }}</div>
                        @endforeach
                    </div>
                </div>
                @error('payment_method_id')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="address-box">
                <div class="address__head">
                    <p class="address__title">配送先</p>
                    <a href="/purchase/address/{{ $item->id }}" class="address__change">変更する</a>
                </div>
                <div class="address">
                    <div class="address__zipcode">
                        <span class="postal-symbol">〒</span>
                        <input type="text" name="shipping_zipcode" class="address__input" value="{{ old('shipping_zipcode', $user->zipcode) }}" readonly>
                    </div>
                    <input type="text" name="shipping_address" class="address__input" value="{{ old('shipping_address', $user->address) }}" readonly>
                    <input type="text" name="shipping_building" class="address__input" value="{{ old('shipping_building', $user->building) }}" readonly>
                </div>
                @error('shipping_address')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="purchase__right">
            <table class="purchase__table">
                <tr class='purchase__table--tr'>
                    <th class="purchase__table--th">商品代金</th>
                    <td class="purchase__table--td">
                        <span class="yen__td">¥</span>
                        <p class="item-price__td">{{ number_format($item->price) }}</p>
                    </td>
                </tr>
                <tr class='purchase__table--tr'>
                    <th class="purchase__table--th">支払い方法</th>
                    <td class="purchase__table--td" id="selectedPaymentMethod"></td>
                </tr>
            </table>
            <input type="hidden" name="item_id" value="{{ $item->id }}">
            <button type="submit" class="buy-button">購入する</button>
        </div>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('payment_method_id');
        const customSelect = document.querySelector('.custom-select');
        const trigger = customSelect.querySelector('.custom-select__trigger');
        const options = customSelect.querySelector('.custom-options');
        const customOptions = options.querySelectorAll('.custom-option');
        const display = document.getElementById('selectedPaymentMethod');

    trigger.addEventListener('click', () => {
        options.style.display = options.style.display === 'block' ? 'none' : 'block';
    });

    customOptions.forEach(option => {
        option.addEventListener('click', () => {
            const text = option.textContent;
            const value = option.getAttribute('data-value');

        select.value = value;

        trigger.textContent = text;
        display.textContent = text;

        options.style.display = 'none';
        });
    });

    const initialText = select.options[select.selectedIndex]?.text;
    display.textContent = (select.value === "") ? "" : initialText;

    select.addEventListener('change', function () {
        const selectedText = this.options[this.selectedIndex].text;
        display.textContent = selectedText;
    });

    document.addEventListener('click', (e) => {
        if (!customSelect.contains(e.target)) {
            options.style.display = 'none';
        }
    });
});
</script>
@endsection