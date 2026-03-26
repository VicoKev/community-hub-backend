<x-email>
    <h3>Bonjour {{ $user->first_name }},</h3>

    <p>Nous avons d'excellentes nouvelles !</p>

    <p>Votre profil a été examiné et <strong>approuvé</strong> par notre équipe d'administration. Vous avez désormais accès à l'ensemble des fonctionnalités de la plateforme Community Hub correspondantes à votre profil.</p>

    <div style="text-align:center; margin: 20px 0;">
        <a href="{{ config('app.frontend_url') }}/dashboard" 
           style="display:inline-block; padding:12px 25px; font-size:16px; color:#fff; background-color:#34c38f; border-radius:5px; text-decoration:none;">
           Accéder à mon espace
        </a>
    </div>

    <p>Merci de faire partie de notre communauté !</p>
</x-email>
