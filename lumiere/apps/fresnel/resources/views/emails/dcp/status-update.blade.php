{{-- Template email statut DCP --}}
<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #2563eb;">DCPrism - Statut DCP</h1>
        <p><strong>Film :</strong> {{ $movie->title }}</p>
        <p><strong>DCP :</strong> #{{ $dcp->id }}</p>
        <div style="background: #f9f9f9; padding: 15px; margin: 20px 0;">
            <p>{{ $message }}</p>
        </div>
        <p><a href="{{ $dashboard_url }}" style="background: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Voir détails</a></p>
    </div>
</body>
</html>
