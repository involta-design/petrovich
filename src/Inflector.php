<?php

namespace Involta\Petrovich;

class Inflector
{
    public function inflect($name, $case, $type): string
    {
        $names_arr = explode('-', $name);
        $result = array();

        foreach ($names_arr as $arr_name) {
            if ($exception = $this->checkException($arr_name, $case, $type)) {
                $result[] = $exception;
            } else {
                $result[] = $this->findInRules($arr_name, $case, $type);
            }
        }
        return implode('-', $result);
    }


    private function checkException($name, $case, $type): bool
    {
        if (!isset($this->rules[$type]->exceptions)) {
            return false;
        }

        $lower_name = mb_strtolower($name);

        foreach ($this->rules[$type]->exceptions as $rule) {
            if (!$this->checkGender($rule->gender)) {
                continue;
            }
            if (\in_array($lower_name, $rule->test, true)) {
                if ($rule->mods[$case] === '.') {
                    return $name;
                }
                return $this->applyRule($rule->mods, $name, $case);
            }
        }
        return false;
    }
}