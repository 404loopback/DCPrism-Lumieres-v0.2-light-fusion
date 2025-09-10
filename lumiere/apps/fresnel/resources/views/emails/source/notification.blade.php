{{-- Template email notification source --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notification DCPrism</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #2563eb;">DCPrism - Notification</h1>
        
        <p>Bonjour,</p>
        
        <p><strong>Film :</strong> {{ $movie->title }}</p>
        
        <div style="background: #f9f9f9; padding: 15px; border-left: 4px solid #2563eb; margin: 20px 0;">
            <p>{{ $message }}</p>
        </div>
        
        <p><a href="{{ $dashboard_url }}" style="background: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Voir mon espace</a></p>
        
        <hr style="margin: 30px 0;">
        <p style="color: #666; font-size: 12px;">Cet email a été généré automatiquement par DCPrism.</p>
    </div>
</body>
</html>
