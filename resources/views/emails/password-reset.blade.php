<x-email>
    <h3>Bonjour {{ $user['first_name'] }},</h3>

    <p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le bouton ci-dessous pour définir un nouveau mot de passe :</p>

    <div style="text-align:center; margin: 20px 0;">
        <a href="{{ $resetLink }}" 
           style="display:inline-block; padding:12px 25px; font-size:16px; color:#fff; background-color:#556ee6; border-radius:5px; text-decoration:none;">
           Réinitialiser mon mot de passe
        </a>
    </div>

    <p>Si vous n'avez pas demandé cette réinitialisation, ignorez simplement cet e-mail. Votre mot de passe restera inchangé.</p>

    <p>Ce lien est valable pendant 60 minutes.</p>
</x-email>
