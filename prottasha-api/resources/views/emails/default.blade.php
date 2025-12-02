@extends('email_template')

@section('title', config('app.name'))

@section('logo')
    <img src="{{ config('app.url') }}/storage/logo/logo.png" alt="{{ config('app.name') }}" style="max-width: 200px;">
@endsection

@section('content')
    {!! $content !!}
@endsection

@section('footer_content')
    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
@endsection