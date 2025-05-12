<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title inertia>{{ config('app.name', 'Win to Win jobs') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="{{asset('fontawesome-free-6.5.1-web/css/all.min.css')}}">

                <!-- PWA  -->
        <meta name="theme-color" content="#ffffff"/>
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="/SmallTile.scale-150.png">
        <link rel="apple-touch-icon" sizes="16x16" href="{{ asset('16.png') }}">
        <link rel="apple-touch-icon" sizes="20x20" href="{{ asset('20.png') }}">
        <link rel="apple-touch-icon" sizes="29x29" href="{{ asset('29.png') }}">
        <link rel="apple-touch-icon" sizes="32x32" href="{{ asset('32.png') }}">
        <link rel="apple-touch-icon" sizes="40x40" href="{{ asset('40.png') }}">
        <link rel="apple-touch-icon" sizes="50x50" href="{{ asset('50.png') }}">
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('57.png') }}">
        <link rel="apple-touch-icon" sizes="58x58" href="{{ asset('58.png') }}">
        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('60.png') }}">
        <link rel="apple-touch-icon" sizes="64x64" href="{{ asset('64.png') }}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('72.png')}}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('76.png')}}">
        <link rel="apple-touch-icon" sizes="80x80" href="{{ asset('80.png')}}">
        <link rel="apple-touch-icon" sizes="87x87" href="{{ asset('87.png')}}">
        <link rel="apple-touch-icon" sizes="100x100" href="{{ asset('100.png')}}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('114.png')}}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('120.png')}}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('144.png')}}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('152.png')}}">
        <link rel="apple-touch-icon" sizes="167x167" href="{{ asset('167.png')}}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('180.png')}}">
        <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('192.png')}}">
        <link rel="apple-touch-icon" sizes="256x256" href="{{ asset('256.png')}}">
        <link rel="apple-touch-icon" sizes="512x512" href="{{ asset('512.png')}}">
        <link rel="apple-touch-icon" sizes="1024x1024" href="{{ asset('1024.png')}}">
        <link rel="icon" type="image/png" sizes="150x150"  href="{{ asset('SmallTile.scale-150.png')}}">
        <link rel="icon" type="image/png" sizes="512x512"  href="{{ asset('android-launchericon-512-512.png')}}">
        <link rel="icon" type="image/png" sizes="192x192"  href="{{ asset('android-launchericon-192-192.png')}}">
        <link rel="icon" type="image/png" sizes="144x144"  href="{{ asset('android-launchericon-144-144.png')}}">
        <link rel="icon" type="image/png" sizes="96x96"  href="{{ asset('android-launchericon-96-96.png')}}">
        <link rel="icon" type="image/png" sizes="72x72"  href="{{ asset('android-launchericon-72-72.png')}}">
        <link rel="icon" type="image/png" sizes="48x48"  href="{{ asset('android-launchericon-48-48.png')}}">
        <link rel="icon" type="image/png" sizes="256x256"  href="{{ asset('Square44x44Logo.altform-lightunplated_targetsize-256.png')}}">
        <link rel="icon" type="image/png" sizes="96x96"  href="{{ asset('Square44x44Logo.altform-lightunplated_targetsize-96.png')}}">
        <link rel="icon" type="image/png" sizes="80x80"  href="{{ asset('Square44x44Logo.altform-lightunplated_targetsize-80.png')}}">
        <link rel="icon" type="image/png" sizes="72x72"  href="{{ asset('Square44x44Logo.altform-lightunplated_targetsize-72.png')}}">
        <link rel="icon" type="image/png" sizes="64x64"  href="{{ asset('Square44x44Logo.altform-lightunplated_targetsize-64.png')}}">
        <link rel="icon" type="image/png" sizes="60x60"  href="{{ asset('Square44x44Logo.altform-lightunplated_targetsize-60.png')}}">
        <link rel="icon" type="image/png" sizes="48x48"  href="{{ asset('Square44x44Logo.altform-lightunplated_targetsize-48.png')}}">
        <link rel="icon" type="image/png" sizes="44x44"  href="{{ asset('Square44x44Logo.altform-lightunplated_targetsize-44.png')}}">
        <link rel="icon" type="image/png" sizes="40x40"  href="{{ asset('Square44x44Logo.altform-lightunplated_targetsize-40.png')}}">
        <link rel="icon" type="image/png" sizes="36x36"  href="{{ asset('Square44x44Logo.altform-lightunplated_targetsize-36.png')}}">
        <link rel="icon" type="image/png" sizes="32x32"  href="{{ asset('Square44x44Logo.altform-lightunplated_targetsize-32.png')}}">
        <link rel="icon" type="image/png" sizes="30x30"  href="{{ asset('Square44x44Logo.altform-lightunplated_targetsize-30.png')}}">
        <link rel="icon" type="image/png" sizes="24x24"  href="{{ asset('Square44x44Logo.altform-lightunplated_targetsize-24.png')}}">
        <link rel="icon" type="image/png" sizes="20x20"  href="{{ asset('Square44x44Logo.altform-lightunplated_targetsize-20.png')}}">
        <link rel="icon" type="image/png" sizes="16x16"  href="{{ asset('Square44x44Logo.altform-lightunplated_targetsize-16.png')}}">
        <link rel="icon" type="image/png" sizes="256x256"  href="{{ asset('Square44x44Logo.altform-unplated_targetsize-256.png')}}">
        <link rel="icon" type="image/png" sizes="96x96"  href="{{ asset('Square44x44Logo.altform-unplated_targetsize-96.png')}}">
        <link rel="icon" type="image/png" sizes="80x80"  href="{{ asset('Square44x44Logo.altform-unplated_targetsize-80.png')}}">
        <link rel="icon" type="image/png" sizes="72x72"  href="{{ asset('Square44x44Logo.altform-unplated_targetsize-72.png')}}">
        <link rel="icon" type="image/png" sizes="64x64"  href="{{ asset('Square44x44Logo.altform-unplated_targetsize-64.png')}}">
        <link rel="icon" type="image/png" sizes="60x60"  href="{{ asset('Square44x44Logo.altform-unplated_targetsize-60.png')}}">
        <link rel="icon" type="image/png" sizes="48x48"  href="{{ asset('Square44x44Logo.altform-unplated_targetsize-48.png')}}">
        <link rel="icon" type="image/png" sizes="44x44"  href="{{ asset('Square44x44Logo.altform-unplated_targetsize-44.png')}}">
        <link rel="icon" type="image/png" sizes="40x40"  href="{{ asset('Square44x44Logo.altform-unplated_targetsize-40.png')}}">
        <link rel="icon" type="image/png" sizes="36x36"  href="{{ asset('Square44x44Logo.altform-unplated_targetsize-36.png')}}">
        <link rel="icon" type="image/png" sizes="32x32"  href="{{ asset('Square44x44Logo.altform-unplated_targetsize-32.png')}}">
        <link rel="icon" type="image/png" sizes="30x30"  href="{{ asset('Square44x44Logo.altform-unplated_targetsize-30.png')}}">
        <link rel="icon" type="image/png" sizes="24x24"  href="{{ asset('Square44x44Logo.altform-unplated_targetsize-24.png')}}">
        <link rel="icon" type="image/png" sizes="20x20"  href="{{ asset('Square44x44Logo.altform-unplated_targetsize-20.png')}}">
        <link rel="icon" type="image/png" sizes="16x16"  href="{{ asset('Square44x44Logo.altform-unplated_targetsize-16.png')}}">
        <link rel="icon" type="image/png" sizes="256x256"  href="{{ asset('Square44x44Logo.targetsize-256.png')}}">
        <link rel="icon" type="image/png" sizes="96x96"  href="{{ asset('Square44x44Logo.targetsize-96.png')}}">
        <link rel="icon" type="image/png" sizes="80x80"  href="{{ asset('Square44x44Logo.targetsize-80.png')}}">
        <link rel="icon" type="image/png" sizes="72x72"  href="{{ asset('Square44x44Logo.targetsize-72.png')}}">
        <link rel="icon" type="image/png" sizes="64x64"  href="{{ asset('Square44x44Logo.targetsize-64.png')}}">
        <link rel="icon" type="image/png" sizes="60x60"  href="{{ asset('Square44x44Logo.targetsize-60.png')}}">
        <link rel="icon" type="image/png" sizes="48x48"  href="{{ asset('Square44x44Logo.targetsize-48.png')}}">
        <link rel="icon" type="image/png" sizes="44x44"  href="{{ asset('Square44x44Logo.targetsize-44.png')}}">
        <link rel="icon" type="image/png" sizes="40x40"  href="{{ asset('Square44x44Logo.targetsize-40.png')}}">
        <link rel="icon" type="image/png" sizes="36x36"  href="{{ asset('Square44x44Logo.targetsize-36.png')}}">
        <link rel="icon" type="image/png" sizes="32x32"  href="{{ asset('Square44x44Logo.targetsize-32.png')}}">
        <link rel="icon" type="image/png" sizes="30x30"  href="{{ asset('Square44x44Logo.targetsize-30.png')}}">
        <link rel="icon" type="image/png" sizes="24x24"  href="{{ asset('Square44x44Logo.targetsize-24.png')}}">
        <link rel="icon" type="image/png" sizes="20x20"  href="{{ asset('Square44x44Logo.targetsize-20.png')}}">
        <link rel="icon" type="image/png" sizes="16x16"  href="{{ asset('Square44x44Logo.targetsize-16.png')}}">
        <link rel="icon" type="image/png" sizes="2480x1200"  href="{{ asset('SplashScreen.scale-400.png')}}">
        <link rel="icon" type="image/png" sizes="1240x600"  href="{{ asset('SplashScreen.scale-200.png')}}">
        <link rel="icon" type="image/png" sizes="930x450"  href="{{ asset('SplashScreen.scale-150.png')}}">
        <link rel="icon" type="image/png" sizes="775x375"  href="{{ asset('SplashScreen.scale-125.png')}}">
        <link rel="icon" type="image/png" sizes="620x300"  href="{{ asset('SplashScreen.scale-100.png')}}">
        <link rel="icon" type="image/png" sizes="200x200"  href="{{ asset('StoreLogo.scale-400.png')}}">
        <link rel="icon" type="image/png" sizes="100x100"  href="{{ asset('StoreLogo.scale-200.png')}}">
        <link rel="icon" type="image/png" sizes="75x75"  href="{{ asset('StoreLogo.scale-150.png')}}">
        <link rel="icon" type="image/png" sizes="63x63"  href="{{ asset('StoreLogo.scale-125.png')}}">
        <link rel="icon" type="image/png" sizes="50x50"  href="{{ asset('StoreLogo.scale-100.png')}}">
        <link rel="icon" type="image/png" sizes="176x176"  href="{{ asset('Square44x44Logo.scale-400.png')}}">
        <link rel="icon" type="image/png" sizes="88x88"  href="{{ asset('Square44x44Logo.scale-200.png')}}">
        <link rel="icon" type="image/png" sizes="66x66"  href="{{ asset('Square44x44Logo.scale-150.png')}}">
        <link rel="icon" type="image/png" sizes="55x55"  href="{{ asset('Square44x44Logo.scale-125.png')}}">
        <link rel="icon" type="image/png" sizes="44x44"  href="{{ asset('Square44x44Logo.scale-100.png')}}">
        <link rel="icon" type="image/png" sizes="1240x1240"  href="{{ asset('LargeTile.scale-400.png')}}">
        <link rel="icon" type="image/png" sizes="620x620"  href="{{ asset('LargeTile.scale-200.png')}}">
        <link rel="icon" type="image/png" sizes="465x465"  href="{{ asset('LargeTile.scale-150.png')}}">
        <link rel="icon" type="image/png" sizes="388x388"  href="{{ asset('LargeTile.scale-125.png')}}">
        <link rel="icon" type="image/png" sizes="310x310"  href="{{ asset('LargeTile.scale-100.png')}}">
        <link rel="icon" type="image/png" sizes="1240x600"  href="{{ asset('Wide310x150Logo.scale-400.png')}}">
        <link rel="icon" type="image/png" sizes="620x300"  href="{{ asset('Wide310x150Logo.scale-200.png')}}">
        <link rel="icon" type="image/png" sizes="465x225"  href="{{ asset('Wide310x150Logo.scale-150.png')}}">
        <link rel="icon" type="image/png" sizes="388x188"  href="{{ asset('Wide310x150Logo.scale-125.png')}}">
        <link rel="icon" type="image/png" sizes="310x150"  href="{{ asset('Wide310x150Logo.scale-100.png')}}">
        <link rel="icon" type="image/png" sizes="600x600"  href="{{ asset('Square150x150Logo.scale-400.png')}}">
        <link rel="icon" type="image/png" sizes="300x300"  href="{{ asset('Square150x150Logo.scale-200.png')}}">
        <link rel="icon" type="image/png" sizes="225x225"  href="{{ asset('Square150x150Logo.scale-150.png')}}">
        <link rel="icon" type="image/png" sizes="188x188"  href="{{ asset('Square150x150Logo.scale-125.png')}}">
        <link rel="icon" type="image/png" sizes="150x150"  href="{{ asset('Square150x150Logo.scale-100.png')}}">
        <link rel="icon" type="image/png" sizes="284x284"  href="{{ asset('SmallTile.scale-400.png')}}">
        <link rel="icon" type="image/png" sizes="142x142"  href="{{ asset('SmallTile.scale-200.png')}}">
        <link rel="icon" type="image/png" sizes="107x107"  href="{{ asset('SmallTile.scale-150.png')}}">
        <link rel="icon" type="image/png" sizes="89x89"  href="{{ asset('SmallTile.scale-125.png')}}">
        <link rel="icon" type="image/png" sizes="71x71"  href="{{ asset('SmallTile.scale-100.png')}}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png')}}">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png')}}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png')}}">
        <link rel="manifest" href="{{ asset('/manifest.json') }}">

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
        <script src="{{ asset('/sw.js') }}"></script>
        <script>
        if ("serviceWorker" in navigator) {
            // Register a service worker hosted at the root of the
            // site using the default scope.
            navigator.serviceWorker.register("/sw.js").then(
            (registration) => {
                console.log("Service worker registration succeeded:", registration);
            },
            (error) => {
                console.error(`Service worker registration failed: ${error}`);
            },
            );
        } else {
            console.error("Service workers are not supported.");
        }
        </script>
    </body>
    <footer-component>

    </footer-component>
</html>
