<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light" data-sidebar="dark"
    data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>
    <title>{{ config('app.name') }}</title>
</head>

<body style="margin: 0; padding: 0; font-family: 'Roboto', sans-serif; background-color: #f8f9fa;">
    <div class="main-content" style="width: 100%; display: flex; justify-content: center; padding: 40px 0;">
        <div class="email-container" style="max-width: 600px; width: 100%; margin: 0 auto; padding: 20px;">
            <div class="email-card"
                style="background-color: #fff; border-radius: 7px; box-shadow: 0 3px 15px rgba(30,32,37,.06); padding: 30px;">

                {{-- Logo --}}
                <div style="text-align: center; margin-bottom: 15px;">
                    {{-- <img src="{{ asset('assets/images/logo-light.png') }}" alt="Logo" height="40"> --}}
                </div>

                {{-- HR --}}
                <div style="border-top: 1px solid #e9ebec; width: 100%; margin: 20px 0;"></div>

                {{ $slot }}

                {{-- HR --}}
                <div style="border-top: 1px solid #e9ebec; width: 100%; margin: 20px 0;"></div>

                {{-- Footer info --}}
                <div style="text-align: center; font-size: 14px; color: #878a99;">
                    Si vous avez reçu cet e-mail par erreur, vous pouvez simplement l’ignorer. Aucune action ne sera
                    effectuée dans Community Hub sans intervention de votre part.
                </div>
            </div>

            {{-- Copyright --}}
            <div style="text-align: center; font-size: 14px; color: #98a6ad; margin-top: 20px;">
                @php
                    $startYear = 2026;
                    $currentYear = date('Y');
                @endphp
                &copy; {{ $startYear }}{{ $startYear != $currentYear ? ' - ' . $currentYear : '' }} Community Hub.
                Tous droits réservés.
            </div>
        </div>
    </div>
</body>


</html>
