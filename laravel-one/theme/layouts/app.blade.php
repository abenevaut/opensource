<!DOCTYPE html>
<html lang="{{ $language }}">
    <head>
        <meta charset="UTF-8">
        <meta name="robots" content="index, follow">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $seo['title'] }}</title>
        <meta name="description" content="{{ $seo['description'] }}">
        <meta name="keywords" content="{{ $seo['keywords'] }}">
        <meta name="author" content="{{ $seo['author'] }}">

        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ $seo['url'] }}">
        <meta property="og:title" content="{{ $seo['title'] }}">
        <meta property="og:description" content="{{ $seo['description'] }}">
        <meta property="og:image" content="{{ $seo['og-image'] }}">

        <meta property="twitter:card" content="summary_large_image">
        <meta name=”twitter:creator” content=”{{ $seo['twitter'] }}”>
        <meta property="twitter:url" content="{{ $seo['url'] }}">
        <meta property="twitter:title" content="{{ $seo['title'] }}">
        <meta property="twitter:description" content="{{ $seo['description'] }}">
        <meta property="twitter:image" content="{{ $seo['og-image'] }}">
        <meta property="twitter:image:alt" content="{{ $seo['description'] }}">

        <base href="/opensource/laravel-one/dist/"> <!-- /opensource/laravel-one/dist/ -->
        <link rel="canonical" href="{{ $seo['url'] }}" />
{{--        <link rel="manifest" href="manifest.webmanifest" crossorigin="use-credentials" />--}}
{{--        <script src="registerSW.js"></script>--}}

        @yield('meta')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="assets/app.css">
        <link rel="shortcut icon" href="assets/app-icon.webp" type="image/webp">
    </head>
    <body class="font-sans antialiased">
        <div id="root"></div>
    </body>
</html>
