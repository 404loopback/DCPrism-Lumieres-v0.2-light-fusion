{{-- Template email alerte monitoring --}}
<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #dc2626;">🚨 Alerte DCPrism</h1>
        <p><strong>Niveau :</strong> {{ $level ?? 'info' }}</p>
        <p><strong>Titre :</strong> {{ $title ?? 'Alerte' }}</p>
        <div style="background: #fef2f2; padding: 15px; border-left: 4px solid #dc2626; margin: 20px 0;">
            <p>{{ $message ?? 'Aucun détail' }}</p>
        </div>
        <p><em>Timestamp: {{ $timestamp ?? now() }}</em></p>
    </div>
</body>
</html>
