{{-- Template email contact --}}
<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1>DCPrism - Message de contact</h1>
        @foreach($contactData ?? [] as $key => $value)
            <p><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</p>
        @endforeach
    </div>
</body>
</html>
