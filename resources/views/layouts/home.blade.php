<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- CSRF Token -->
         {{-- 後の章で説明します --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- 各ページごとにtitleタグを入れるために@yieldで空けておきます。 --}}
        <title>@yield('title')</title>

        <!-- Scripts -->
         {{-- Laravel標準で用意されているJavascriptを読み込みます --}}
        <script src="{{ asset('js/app.js') }}" defer></script>
        <script src="{{ asset('js/message.js') }}" defer></script>
        <!-- Fonts -->
        <link rel="dns-prefetch" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        {{-- Laravel標準で用意されているCSSを読み込みます --}}
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">

        {{-- この章の後半で作成するCSSを読み込みます --}}
        <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    </head>
    <body class="jumbotron">
        <div id="app">
            {{-- 画面上部に表示するナビゲーションバーです。 --}}
            <nav class="navbar navbar-expand-lg navbar-light bg-light p-4">
              <a class="navbar-brand mx-4" href="{{ url('/') }}">{{ config('app.name', 'laravel') }}</a>
                <ul class="navbar-nav">
                  <li class="nav-item active mx-4">
                    <a class="nav-link" href="/user/user/index">フレンド</a>
                  </li>
                  <li class="nav-item mx-4">
                    <a class="nav-link" href="/user/profile/profile">プロフィール</a>
                  </li>
                  {{--<li class="nav-item mx-4">
                        <a class="nav-link" href="/user/home/twitter">Twitter</a>
                      </li>--}}
                </ul>
            </nav>
              {{-- ここまでナビゲーションバー --}}
              <main>
                <div class="container shadow-lg">
                  {{-- コンテンツをここに入れるため、@yieldで空けておきます。 --}}
                  @yield('content')
                </div>
            </main>
        </div>
    </body>
</html>
