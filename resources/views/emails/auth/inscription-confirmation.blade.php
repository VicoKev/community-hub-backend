<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation d'inscription</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background-color: #1a56db; padding: 32px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; }
        .body { padding: 32px; color: #374151; }
        .body h2 { font-size: 18px; margin-top: 0; }
        .badge { display: inline-block; background-color: #fef3c7; color: #92400e; padding: 6px 14px; border-radius: 20px; font-size: 14px; font-weight: bold; margin: 16px 0; }
        .info-box { background-color: #f9fafb; border-left: 4px solid #1a56db; padding: 16px; border-radius: 4px; margin: 24px 0; }
        .info-box p { margin: 4px 0; font-size: 14px; }
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
            <p>Nous avons bien reçu votre demande d'inscription sur la plateforme <strong>Community Hub</strong>.</p>
            <p>Votre compte a été créé avec le statut :</p>
            <span class="badge">En attente de validation</span>
            <p>Pour que votre dossier soit soumis à validation, vous devez <strong>compléter votre profil</strong> en suivant les étapes suivantes :</p>
            <ol style="color: #374151; font-size: 14px; line-height: 2;">
                <li>Renseigner vos informations personnelles (localisation, biographie)</li>
                <li>Ajouter vos compétences et secteur d'activité</li>
                <li>Déposer vos documents requis (CV, photo, documents légaux)</li>
            </ol>
            <p>Une fois votre dossier complet, il sera examiné par un administrateur. Vous recevrez un email de confirmation dès que votre compte sera activé.</p>
            <div class="info-box">
                <p><strong>Récapitulatif de votre inscription :</strong></p>
                <p>Nom : {{ $user->prenom }} {{ $user->nom }}</p>
                <p>Email : {{ $user->email }}</p>
                <p>Date d'inscription : {{ $user->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            <p>Si vous n'êtes pas à l'origine de cette inscription, ignorez cet email.</p>
            <p>Cordialement,<br><strong>L'équipe Community Hub</strong></p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} Community Hub. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
