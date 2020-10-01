<?php

namespace Involta\Petrovich\Detectors;

use Involta\Petrovich\Interfaces\DetectorInterface;

class Russian implements DetectorInterface
{
    private int $gender = DetectorInterface::GENDER_ANDROGYNOUS;
    private string $middlename;

    public function __construct(string $middlename)
    {
        $this->middlename = $middlename;
    }

    public function detect(): bool
    {
        switch (mb_strtolower(mb_substr($this->middlename, -2))) {
            case 'ич':
                $this->gender = self::GENDER_MALE;
                break;
            case 'на':
                $this->gender = self::GENDER_FEMALE;
                break;
        }

        return $this->gender !== DetectorInterface::GENDER_ANDROGYNOUS;
    }

    public function getDetectedGender(): int
    {
        return $this->gender;
    }
}