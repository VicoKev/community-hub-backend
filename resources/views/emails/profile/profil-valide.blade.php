<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte activé</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background-color: #059669; padding: 32px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; }
        .body { padding: 32px; color: #374151; }
        .badge { display: inline-block; background-color: #d1fae5; color: #065f46; padding: 6px 14px; border-radius: 20px; font-size: 14px; font-weight: bold; margin: 16px 0; }
        .cta { display: inline-block; background-color: #059669; color: #ffffff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; margin: 20px 0; }
        .footer { background-color: #f9fafb; padding: 20px 32px; text-align: center; color: #9ca3af; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Community Hub</h1>
        </div>
        <div class="body">
            <h2>Félicitations {{ $user->prenom }} {{ $user->nom }} !</h2>
            <p>Votre dossier a été examiné et validé par notre équipe. Votre compte est maintenant actif.</p>
            <span class="badge">✓ Compte activé</span>
            <p>Vous pouvez dès à présent vous connecter à la plateforme Community Hub et accéder à toutes les fonctionnalités disponibles selon votre profil.</p>
            <p style="color: #6b7280; font-size: 14px;">Date de validation : {{ now()->format('d/m/Y à H:i') }}</p>
            <p>Cordialement,<br><strong>L'équipe Community Hub</strong></p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} Community Hub. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
