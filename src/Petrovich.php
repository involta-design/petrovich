<?php

namespace Involta\Petrovich;

use Involta\Petrovich\Interfaces\DetectorInterface;

final class Petrovich
{

    private $rules; //Правила

    public const CASE_NOMENATIVE = -1; //именительный NM
    public const CASE_GENITIVE = 0; //родительный GN
    public const CASE_DATIVE = 1; //дательный DT
    public const CASE_ACCUSATIVE = 2; //винительный AC
    public const CASE_INSTRUMENTAL = 3; //творительный IN
    public const CASE_PREPOSITIONAL = 4; //предложный PR

    private $gender; //Пол male/мужской female/женский

    private $detectors = [
        Involta\Petrovich\Detectors\Kazakh::class,
        Involta\Petrovich\Detectors\Russian::class
    ];

    public function __construct()
    {
        $rules_path = __DIR__ . '/rules/rules.json';
        $this->gender = DetectorInterface::GENDER_ANDROGYNOUS;

        $rules_resourse = fopen($rules_path, 'rb');
        $rules_array = fread($rules_resourse, filesize($rules_path));
        fclose($rules_resourse);

        $this->rules = get_object_vars(json_decode($rules_array, true));
    }

    public function withGender(int $gender): self
    {
        $new = clone $this;
        $new->gender = $gender;
    }

    /**
     * Определяет пол по отчеству
     * @param $middlename
     * @return integer
     * @throws Exception
     */
    public function detectGender($middlename): ?int
    {
        if (empty($middlename)) {
            throw new Exception('Middlename cannot be empty.');
        }

        foreach ($this->detectors as $detectorClass) {
            /** @var DetectorInterface $detector */
            $detector = new $detectorClass($middlename);

            if($detector->detect()) {
                return $detector->getDetectedGender();
            }
        }

        return DetectorInterface::GENDER_ANDROGYNOUS;
    }

    /**
     * Задаём имя и слоняем его
     *
     * @param $firstname
     * @param $case
     * @return bool|string
     * @throws Exception
     */
    public function firstname($firstname, $case = Petrovich::CASE_NOMENATIVE)
    {
        if (empty($firstname)) {
            throw new Exception('Firstname cannot be empty.');
        }

        if ($case === self::CASE_NOMENATIVE) {
            return $firstname;
        }

        return $this->inflect($firstname, $case, __FUNCTION__);
    }

    /**
     * Задём отчество и склоняем его
     *
     * @param $middlename
     * @param $case
     * @return bool|string
     * @throws Exception
     */
    public function middlename($middlename, $case = Petrovich::CASE_NOMENATIVE)
    {
        if (empty($middlename)) {
            throw new Exception('Middlename cannot be empty.');
        }

        if ($case === self::CASE_NOMENATIVE) {
            return $middlename;
        }

        return $this->inflect($middlename, $case, __FUNCTION__);
    }

    /**
     * Задаём фамилию и слоняем её
     *
     * @param $lastname
     * @param $case
     * @return bool|string
     * @throws Exception
     */
    public function lastname($lastname, $case = Petrovich::CASE_NOMENATIVE)
    {
        if (empty($lastname)) {
            throw new Exception('Lastname cannot be empty.');
        }

        if ($case === self::CASE_NOMENATIVE) {
            return $lastname;
        }

        return $this->inflect($lastname, $case, __FUNCTION__);
    }

    /**
     * Функция проверяет заданное имя,фамилию или отчество на исключение
     * и склоняет
     *
     * @param $name
     * @param $case
     * @param $type
     * @return bool|string
     */
    private function inflect($name, $case, $type)
    {
        $names_arr = explode('-', $name);
        $result = array();

        foreach ($names_arr as $arr_name) {
            if (($exception = $this->checkException($arr_name, $case, $type)) !== false) {
                $result[] = $exception;
            } else {
                $result[] = $this->findInRules($arr_name, $case, $type);
            }
        }
        return implode('-', $result);
    }

    /**
     * Поиск в массиве правил
     *
     * @param $name
     * @param $case
     * @param $type
     * @return string
     */
    private function findInRules($name, $case, $type): string
    {
        foreach ($this->rules[$type]->suffixes as $rule) {
            if (!$this->checkGender($rule->gender)) {
                continue;
            }
            foreach ($rule->test as $last_char) {
                $last_name_char = mb_substr($name, mb_strlen($name) - mb_strlen($last_char), mb_strlen($last_char));
                if ($last_char == $last_name_char) {
                    if ($rule->mods[$case] === '.') {
                        return $name;
                    }
                    return $this->applyRule($rule->mods, $name, $case);
                }
            }
        }
        return $name;
    }

    /**
     * Проверка на совпадение в исключениях
     *
     * @param $name
     * @param $case
     * @param $type
     * @return bool|string
     */
    private function checkException($name, $case, $type)
    {
        if (!isset($this->rules[$type]->exceptions)) {
            return false;
        }

        $lower_name = mb_strtolower($name);

        foreach ($this->rules[$type]->exceptions as $rule) {
            if (!$this->checkGender($rule->gender)) {
                continue;
            }
            if (in_array($lower_name, $rule->test, true)) {
                if ($rule->mods[$case] === '.') {
                    return $name;
                }
                return $this->applyRule($rule->mods, $name, $case);
            }
        }
        return false;
    }

    /**
     * Склоняем заданное слово
     *
     * @param $mods
     * @param $name
     * @param $case
     * @return string
     */
    private function applyRule($mods, $name, $case): string
    {
        $result = mb_substr($name, 0, mb_strlen($name) - mb_substr_count($mods[$case], '-'));
        $result .= str_replace('-', '', $mods[$case]);
        return $result;
    }

    /**
     * Преобразует строковое обозначение пола в числовое
     * @param string
     * @return integer
     */
    private function getGender($genderString): ?int
    {
        return GenderHelper::stringToGender($genderString);
    }

    /**
     * Проверяет переданный пол на соответствие установленному
     * @param string
     * @return bool
     */
    private function checkGender(string $genderString): bool
    {
        $gender = GenderHelper::stringToGender($genderString);

        return $this->gender === $gender || $gender === DetectorInterface::GENDER_ANDROGYNOUS;
    }
}