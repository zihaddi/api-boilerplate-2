@extends('email_template')

@section('title', 'New Contact Form Submission')



@section('content')
    <h2>New Contact Form Submission</h2>

    <div style="background: #f5f5f5; padding: 20px; margin: 20px 0; border-radius: 5px;">
        <h3>Contact Details:</h3>
        <p><strong>Name:</strong> {{ $contact->first_name }} {{ $contact->last_name }}</p>
        <p><strong>Email:</strong> {{ $contact->email }}</p>
        @if($contact->phone)
            <p><strong>Phone:</strong> {{ $contact->phone }}</p>
        @endif
        <p><strong>Subject:</strong> {{ $contact->subject }}</p>
        <p><strong>Message:</strong><br> {{ $contact->message }}</p>
        <p><strong>Submitted at:</strong> {{ $contact->created_at->format('Y-m-d H:i:s') }}</p>
    </div>

    <p>Please review and respond to this inquiry as soon as possible.</p>
@endsection