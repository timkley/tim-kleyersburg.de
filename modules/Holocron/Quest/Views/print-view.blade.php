@extends('holocron-printer::layout')

@section('style')
    <style>
        .breadcrumb {
            color: #333;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .name {
            font-size: 36px;
            font-weight: 700;
            word-wrap: break-word;
            line-height: 1.1;
        }

        .stars {
            line-height: 1;
        }

        .stars--top {
            margin-bottom: 35px;
        }

        .stars--bottom {
            margin-top: 35px;
        }
    </style>
@endsection

@section('content')
    <div class="text-center">
        <div class="stars stars--top">* * * * * * * *</div>
        <div class="breadcrumb">{{ $quest->breadcrumb()->pluck('name')->join(' > ') }}</div>
        <div class="name">{{ $quest->name }}</div>
        <div class="stars stars--bottom">* * * * * * * *</div>
    </div>
@endsection
