@extends('layouts.app')

@section('content')
<p>取引が完了しました。</p>
<p>商品名：{{ $transaction->item->name }}</p>
@endsection