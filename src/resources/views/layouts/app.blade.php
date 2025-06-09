<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__logo">
            <a href="/">
                <img class="header__logo--img" src="{{ asset('logo.svg') }}">
            </a>
            </div>
            @if (!request()->is('register') && !request()->is('login') && !request()->is('verify-email'))
            <div class="header__search">
                <form action="/" class="search-form" method="GET">
                    @csrf
                    <input type="hidden" name="tab" value="{{ isset($tab) ? $tab : 'recommend' }}">
                    <input type="text" name="keyword" value="{{ $keyword ?? '' }}"
                    class="search-form__input" placeholder="なにをお探しですか？">
                    <button type="submit" class="search-form__button">検索</button>
                </form>
            </div>
            <nav class="header__nav">
                <ul class="nav__ul">
                    @if (Auth::check())
                    <li class="nav-list">
                        <form action="/logout" method="POST">
                            @csrf
                            <button type="submit" class="logout-button">ログアウト</button>
                        </form>
                    </li>
                    @else
                    <li class="nav-list"><a href="/login" class="nav-item">ログイン</a></li>
                    @endif
                    <li class="nav-list"><a href="/mypage" class="nav-item">マイページ</a></li>
                    <li class="nav-list--sell"><a href="/sell" class="nav-item--sell">出品</a></li>
                </ul>
            </nav>
            @endif
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>