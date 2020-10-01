<?php

namespace Involta\Petrovich;

use Involta\Petrovich\Interfaces\DetectorInterface;

class GenderHelper
{
    public static function stringToGender(string $genderString): int
    {
        switch ($gender) {
            case 'male':
                return DetectorInterface::GENDER_MALE;
                break;
            case 'female':
                return DetectorInterface::GENDER_FEMALE;
                break;
            case 'androgynous':
                return DetectorInterface::GENDER_ANDROGYNOUS;
                break;
        }
    }
}