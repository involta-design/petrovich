<?php

namespace Involta\Petrovich\Interfaces;

interface DetectorInterface
{
    public const GENDER_ANDROGYNOUS = 0; // Пол не определен

    public const GENDER_MALE = 1; // Мужской

    public const GENDER_FEMALE = 2; // Женский

    public function __construct(string $middlename);

    public function detect(): bool;

    public function getDetectedGender(): int;
}