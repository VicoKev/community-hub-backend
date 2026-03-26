<x-email>
    <h3>Bonjour {{ $user->first_name }},</h3>

    <p>Votre profil a été examiné par notre équipe d'administration.</p>

    <p>Malheureusement, nous ne pouvons pas valider votre profil en l'état actuel et des corrections sont nécessaires.</p>

    <div style="background-color: #fff3cd; color: #856404; padding: 15px; border-left: 4px solid #ffeeba; border-radius: 4px; margin: 20px 0;">
        <strong>Motif du rejet :</strong><br>
        {{ $reason }}
    </div>

    <p>Veuillez vous connecter à votre compte pour mettre à jour vos informations en tenant compte des remarques ci-dessus.</p>

    <div style="text-align:center; margin: 20px 0;">
        <a href="{{ config('app.frontend_url') }}/profile/edit" 
           style="display:inline-block; padding:12px 25px; font-size:16px; color:#fff; background-color:#f1b44c; border-radius:5px; text-decoration:none;">
           Modifier mon profil
        </a>
    </div>
</x-email>
