<?php

namespace App\Enums;

enum ProfileCategory: string
{
    case ADMINISTRATIVE_EXECUTIVE = 'administrative_executive';
    case TECHNICAL_EXECUTIVE = 'technical_executive';
    case BUSINESS_OWNER = 'business_owner';
    case ARTISAN = 'artisan';
    case MERCHANT = 'merchant';
    case YOUNG_ENTREPRENEUR = 'young_entrepreneur';
    case INVESTOR = 'investor';
    case PARTNER = 'partner';

    public function label(): string
    {
        return match($this) {
            self::ADMINISTRATIVE_EXECUTIVE => 'Cadre administratif',
            self::TECHNICAL_EXECUTIVE => 'Cadre technique',
            self::BUSINESS_OWNER => 'Chef d\'entreprise',
            self::ARTISAN => 'Artisan',
            self::MERCHANT => 'Commerçant',
            self::YOUNG_ENTREPRENEUR => 'Jeune entrepreneur',
            self::INVESTOR => 'Investisseur',
            self::PARTNER => 'Partenaire',
        };
    }
}
