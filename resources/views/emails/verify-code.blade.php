<x-email>
    <h3>Bonjour {{ $user['first_name'] }},</h3>

    <p>Voici votre code de vérification :</p>

    <h1 style="background-color:#d3d3d352;text-align:center">
        <a style="text-decoration:none;color:#1c1d1f">{{ $code }}</a>
    </h1>

    <p>Ce code est valable pendant 10 minutes.</p>
</x-email>
