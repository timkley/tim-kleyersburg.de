@extends('holocron-printer::layout')

@section('style')
    <style>
        .wmo {
            filter: brightness(0);
        }
    </style>
@endsection

@section('content')
    <div class="text-center">
        <p>
            {{ $forecast->date->translatedFormat('D d.m.') }}
        </p>
        <p class="text-lg">
            <img class="wmo" src="/img/weather_icons/{{ $forecast->wmoCode }}d_big.png" alt="{{ $forecast->condition }}">
        </p>
        <p>
            {{ $forecast->minTemp }}° / {{ $forecast->maxTemp }}°
        </p>
    </div>
@endsection
