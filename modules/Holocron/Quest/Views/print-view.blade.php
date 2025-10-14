@extends('holocron-printer::layout')

@section('style')
    <style>
        .breadcrumb {
            color: #333;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .name {
            font-weight: 700;
            word-wrap: break-word;
            line-height: 1.1;
        }
    </style>
@endsection

@section('content')
    <div class="text-center">
        <div style="margin-bottom: 36px">* * * * * * * *</div>
        <div class="breadcrumb">{{ $quest->breadcrumb()->pluck('name')->join(' > ') }}</div>
        <div class="name font-bold text-lg">{{ $quest->name }}</div>
        @if($quest->description)
            <div class="description" style="margin-top: 32px">
                {!! $quest->description !!}
            </div>
        @endif
        <div style="margin-top: 36px">* * * * * * * *</div>
    </div>
@endsection
