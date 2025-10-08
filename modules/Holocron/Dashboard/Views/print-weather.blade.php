@extends('holocron-printer::layout')

@section('style')
    <style>
        .text-sm {
            font-size: 24px;
        }
        .text-lg {
            font-size: 36px;
        }
        .weather {
            text-align: center;
            margin: auto;
        }

        .wmo {
            filter: brightness(0);
        }
    </style>
@endsection

@section('content')
    <div class="weather">
        <p class="text-sm">
            {{ $forecast->date->translatedFormat('D d.m.') }}
        </p>
        <p class="text-lg">
            <img class="wmo" src="/img/weather_icons/{{ $forecast->wmoCode }}d_big.png" alt="{{ $forecast->condition }}">
        </p>
        <p class="text-sm">
            {{ $forecast->minTemp }}° / {{ $forecast->maxTemp }}°
        </p>
    </div>
@endsection
