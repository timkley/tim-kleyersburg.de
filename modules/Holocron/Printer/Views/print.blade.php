@extends('holocron-printer::layout')

@section('style')
    <style>
        .content {
            font-weight: 700;
            word-wrap: break-word;
            line-height: 1.1;
        }
    </style>
@endsection

@section('content')
    <div class="text-center">
        <div style="margin-bottom: 36px">* * * * * * * *</div>
        <div class="content font-bold text-lg">{{ $content }}</div>
        <div style="margin-top: 36px">* * * * * * * *</div>
    </div>
@endsection
