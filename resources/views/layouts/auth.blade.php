<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Login</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}"/>

        <!-- Immediate Theme Application to prevent FOUC -->
        <script>
            (function() {
                try {
                    const theme = localStorage.getItem('system_theme_mode') || 'dark';
                    if (theme === 'light') {
                        document.documentElement.classList.remove('dark');
                        document.documentElement.classList.add('light');
                    } else {
                        document.documentElement.classList.remove('light');
                        document.documentElement.classList.add('dark');
                    }
                } catch (e) {}
            })();
        </script>

        <!-- Fonts & Icons -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --color-background: 0, 0, 0;
                --color-on-surface: 247, 247, 247;
                --color-surface-dark: 18, 18, 18;
                --color-border: rgba(255, 255, 255, 0.1);
                --color-input-bg: 6, 6, 6;
                --color-primary-text: 247, 247, 247;
                --color-brand-glow: rgba(255, 221, 50, 0.15);
                --color-primary: 255, 221, 50;
            }

            html.light {
                --color-background: 255, 255, 255;
                --color-on-surface: 0, 0, 0;
                --color-surface-dark: 237, 237, 237;
                --color-border: rgba(0, 0, 0, 0.08);
                --color-input-bg: 255, 255, 255;
                --color-primary-text: 0, 0, 0;
                --color-brand-glow: rgba(255, 221, 50, 0.12);
                --color-primary: 255, 221, 50;
            }

            body {
                background-color: rgb(var(--color-background));
                color: rgb(var(--color-on-surface));
            }
        </style>
    </head>
    <body class="font-sans antialiased min-h-screen">
        {{ $slot }}
    </body>
</html>
