<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        
        <!-- Scripts -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Figtree', 'sans-serif'],
                        },
                    }
                }
            }
        </script>
        <!-- @vite(['resources/css/app.css', 'resources/js/app.js']) -->
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex">
            <!-- Left Side: Image -->
            <div class="hidden lg:block w-1/2 relative bg-gray-900">
                <img src="{{ asset('assets/images/bg-login.jpg') }}" alt="Background" class="absolute inset-0 w-full h-full object-cover text-transparent opacity-80">
                <div class="absolute inset-0 bg-gradient-to-r from-amber-900/60 to-black/40"></div>
                <div class="absolute bottom-0 left-0 p-12 text-white z-10">
                    <h2 class="text-4xl font-bold mb-4">Welcome to Mutiara Harapan</h2>
                    <p class="text-lg text-gray-200">Manage your events and registrations with ease.</p>
                </div>
            </div>

            <!-- Right Side: Form -->
            <div class="w-full lg:w-1/2 flex items-center justify-center bg-white px-8 py-12 sm:px-12 lg:px-16 xl:px-24">
                <div class="w-full max-w-md space-y-8">
                    <div class="flex justify-center mb-8">
                        <a href="/">
                            <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="h-24 w-auto drop-shadow-md hover:scale-105 transition-transform duration-300">
                        </a>
                    </div>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
