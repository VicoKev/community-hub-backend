<x-email>
    <h3>Bonjour {{ $admin->first_name }},</h3>

    <p>Un nouvel utilisateur vient de soumettre son profil pour validation sur la plateforme.</p>

    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <strong>Utilisateur :</strong> {{ $user->first_name }} {{ $user->last_name }}<br>
        <strong>Email :</strong> {{ $user->email }}<br>
        <strong>Catégorie :</strong> {{ $profile->categoryLabel }}
    </div>

    <p>Veuillez vous connecter à l'interface d'administration pour examiner et valider ce profil.</p>

    <div style="text-align:center; margin: 20px 0;">
        <a href="{{ config('app.frontend_url') }}/admin/profiles/{{ $profile->id }}" 
           style="display:inline-block; padding:12px 25px; font-size:16px; color:#fff; background-color:#556ee6; border-radius:5px; text-decoration:none;">
           Voir le profil
        </a>
    </div>
</x-email>
