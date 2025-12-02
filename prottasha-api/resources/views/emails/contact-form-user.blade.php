@extends('email_template')

@section('title', 'Thank you for contacting us')

@section('logo')
    <img src="{{ config('app.url') }}/storage/logo/logo.png" alt="{{ config('app.name') }}" style="max-width: 200px;">
@endsection

@section('content')
    <h2>Thank you for contacting us!</h2>
    
    <p>Dear {{ $contact->first_name }},</p>
    
    <p>We have received your message and will get back to you as soon as possible.</p>
    
    <div style="background: #f5f5f5; padding: 20px; margin: 20px 0; border-radius: 5px;">
        <h3>Your Message Details:</h3>
        <p><strong>Subject:</strong> {{ $contact->subject }}</p>
        <p><strong>Message:</strong><br> {{ $contact->message }}</p>
    </div>
    
    <p>If you have any additional questions, please don't hesitate to reach out.</p>
@endsection

@section('footer_content')
    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
@endsection
