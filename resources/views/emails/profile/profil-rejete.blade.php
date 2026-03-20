<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dossier {{ $statut === 'suspendu' ? 'suspendu' : 'rejeté' }}</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background-color: #dc2626; padding: 32px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; }
        .body { padding: 32px; color: #374151; }
        .badge { display: inline-block; background-color: #fee2e2; color: #991b1b; padding: 6px 14px; border-radius: 20px; font-size: 14px; font-weight: bold; margin: 16px 0; }
        .motif-box { background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 16px; border-radius: 4px; margin: 20px 0; }
        .motif-box p { margin: 0; font-size: 14px; color: #7f1d1d; }
        .footer { background-color: #f9fafb; padding: 20px 32px; text-align: center; color: #9ca3af; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Community Hub</h1>
        </div>
        <div class="body">
            <h2>Bonjour {{ $user->prenom }} {{ $user->nom }},</h2>

            @if($statut === 'suspendu')
                <p>Nous vous informons que votre compte sur la plateforme Community Hub a été <strong>suspendu</strong>.</p>
                <span class="badge">Compte suspendu</span>
            @else
                <p>Après examen de votre dossier, nous sommes dans l'obligation de vous informer que votre demande d'adhésion a été <strong>rejetée</strong>.</p>
                <span class="badge">Dossier rejeté</span>
            @endif

            @if($motif)
                <div class="motif-box">
                    <p><strong>Motif :</strong> {{ $motif }}</p>
                </div>
            @endif

            <p>Si vous pensez qu'il s'agit d'une erreur ou souhaitez obtenir plus d'informations, veuillez contacter notre équipe.</p>
            <p>Cordialement,<br><strong>L'équipe Community Hub</strong></p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} Community Hub. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
