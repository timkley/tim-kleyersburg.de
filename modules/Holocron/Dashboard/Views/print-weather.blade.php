@extends('holocron-printer::layout')

@section('style')
    <style>
        .wmo {
            filter: brightness(0);
        }
    </style>
@endsection

@section('content')
    @php
        $iconPath = public_path("img/weather_icons/{$forecast->wmoCode}d_big.png");
        $iconBase64 = file_exists($iconPath) ? base64_encode(file_get_contents($iconPath)) : null;
    @endphp
    <div class="text-center">
        <p>
            {{ $forecast->date->translatedFormat('D d.m.') }}
        </p>
        <p class="text-lg">
            @if($iconBase64)
                <img class="wmo" src="data:image/png;base64,{{ $iconBase64 }}" alt="{{ $forecast->condition }}">
            @else
                <span>{{ $forecast->condition }}</span>
            @endif
        </p>
        <p>
            {{ $forecast->minTemp }}° / {{ $forecast->maxTemp }}°
        </p>
    </div>
@endsection
