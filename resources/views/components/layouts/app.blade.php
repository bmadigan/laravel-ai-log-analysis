<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif


    </head>
    <body class="min-h-screen bg-zinc-50">
        <flux:header container class="bg-white border-b border-zinc-200 px-12 py-6">
            <a href="{{ route('home') }}" class="h-10 flex items-center gap-3" aria-label="LogAnalysis">
                <span class="inline-flex items-center justify-center bg-zinc-900 text-white text-xs font-semibold rounded-md px-2 py-1">AI</span>
                <span class="text-sm font-medium text-zinc-800">LogAnalysis</span>
            </a>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
    </html>
