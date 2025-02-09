<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form Submission</title>
</head>

<body>
    <p><strong>Contact Form Submission:</strong></p>
    <p><strong>Name:</strong> {{ $contact->name }}</p>
    <p><strong>Email:</strong> {{ $contact->email }}</p>
    <p><strong>Message:</strong></p>
    <p>{{ $contact->message }}</p>
    <p><small>Submitted at: {{ $contact->created_at->format('Y-m-d H:i:s') }}</small></p>
</body>

</html>
