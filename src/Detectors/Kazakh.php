<?php

namespace Involta\Petrovich\Detectors;

use Involta\Petrovich\Interfaces\DetectorInterface;

class Kazakh implements DetectorInterface
{
    private int $gender = DetectorInterface::GENDER_ANDROGYNOUS;
    private string $middlename;

    public function __construct(string $middlename)
    {
        $this->middlename = $middlename;
    }

    public function detect(): bool
    {
        switch (mb_strtolower(mb_substr($this->middlename, -4))) {
            case 'оглы':
                $this->gender = DetectorInterface::GENDER_MALE;
                break;
            case 'кызы':
                $this->gender = DetectorInterface::GENDER_FEMALE;
                break;
        }

        return $this->gender !== DetectorInterface::GENDER_ANDROGYNOUS;
    }

    public function getDetectedGender(): int
    {
        return $this->gender;
    }
}