{{-- Template email création compte Source --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Votre compte DCPrism</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #2563eb;">Bienvenue sur DCPrism</h1>
        
        <p>Bonjour {{ $user->name }},</p>
        
        <p>Votre compte DCPrism a été créé avec succès. Voici vos identifiants de connexion :</p>
        
        <div style="background: #f3f4f6; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Email :</strong> {{ $user->email }}</p>
            <p><strong>Mot de passe temporaire :</strong> {{ $password }}</p>
        </div>
        
        <p><a href="{{ $login_url }}" style="background: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Se connecter</a></p>
        
        <p><em>Merci de changer votre mot de passe lors de votre première connexion.</em></p>
        
        <hr style="margin: 30px 0;">
        <p style="color: #666; font-size: 12px;">Cet email a été généré automatiquement par DCPrism.</p>
    </div>
</body>
</html>
