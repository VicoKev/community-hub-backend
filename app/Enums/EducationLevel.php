<?php

namespace App\Enums;

enum EducationLevel: string
{
    case NONE = 'none';
    case PRIMARY = 'primary';
    case MIDDLE = 'middle';
    case HIGH_SCHOOL = 'high_school';
    case BACHELOR = 'bachelor';
    case MASTER = 'master';
    case PHD = 'phd';
    case PROFESSIONAL = 'professional';

    public function label(): string
    {
        return match($this) {
            self::NONE => 'Aucun diplôme',
            self::PRIMARY => 'Primaire',
            self::MIDDLE => 'Collège',
            self::HIGH_SCHOOL => 'Lycée / Baccalauréat',
            self::BACHELOR => 'Licence / Bachelor',
            self::MASTER => 'Master',
            self::PHD => 'Doctorat',
            self::PROFESSIONAL => 'Formation professionnelle',
        };
    }
}
