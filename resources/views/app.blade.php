<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
        <title inertia>{{ config('app.name', 'Win to Win jobs') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="{{asset('fontawesome-free-6.5.1-web/css/all.min.css')}}">

                <!-- PWA  -->
        <meta name="theme-color" content="#ffffff"/>
        <meta name="msapplication-TileColor" content="#ffffff">
        <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('192.png')}}">
        <link rel="apple-touch-icon" sizes="512x512" href="{{ asset('512.png')}}">
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">

        <!-- Scripts -->
        @routes
        @vite('resources/js/app.js')
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
        <footer-component></footer-component>
    </body>
</html>
