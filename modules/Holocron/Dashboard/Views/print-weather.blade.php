@extends('holocron-printer::layout')

@section('style')
    <style>
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
        <p>
            {{ $forecast->date->translatedFormat('D d.m.') }}
        </p>
        <p>
            <img class="wmo" src="/img/weather_icons/{{ $forecast->wmoCode }}d_big.png" alt="{{ $forecast->condition }}">
        </p>
        <p>
            {{ $forecast->minTemp }}° / {{ $forecast->maxTemp }}°
        </p>
    </div>
@endsection
