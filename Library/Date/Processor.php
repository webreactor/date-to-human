<?php

namespace Library\Date;

class Processor {
    private $rules;
    private $params;

    public function initRules(array $rules) {
        $this->rules = $rules;
        foreach ($this->rules['templates'] as &$template) {
            if (isset($template['from'])) {
                $template['from'] = strtotime($template['from'], 0);
            }
            if (isset($template['till'])) {
                $template['till'] = strtotime($template['till'], 0);
            }
            if (!isset($template['params'])) {
                $template['params'] = array();
            }
        }
        $this->params = array(
            "%hour_unit" => array("%cnt час", "%cnt часа", "%cnt часов"),
            "%minute_unit" => array("%cnt минуту", "%cnt минуты", "%cnt минут"),
            "%second_unit" => array("%cnt секунду", "%cnt секунды", "%cnt секунд"),
            "%day_unit" => array("%cnt день", "%cnt дня", "%cnt дней"),
            "%week_unit" => array("%cnt неделя", "%cnt недели", "%cnt недель"),
            "%month_unit" => array("%cnt месяц", "%cnt дня", "%cnt дней"),
            "%year_unit" => array("%cnt день", "%cnt дня", "%cnt дней"),
            "ago" => "%datetime назад",
            "later" => "через %datetime",
            "before" => "%datetime до",
            "after" => "%datetime после");
    }

    public function translate(\DateTime $dt, $relative, array $units, $seconds, $calendar) {
        $template = $this->getTemplate($seconds, $calendar);
        $search = $replace = array();
        foreach ($template['params'] as $param_name => $param_value) {
            $search[] = $param_name;
            $replace[] = $dt->format($param_value);
        }
        foreach ($units as $key => $value) {
            $search[] = $key;
            $replace[] = $this->getCountWithUnit($value, $this->getParam($key . "_unit"));
        }
        $result = str_replace($search, $replace, $template['format']);
        if ($relative !== false) {
            $result = str_replace("%datetime", $result, $this->getParam($relative));
        }
        return $result;
    }

    private function getTemplate($seconds, $calendar) {
        foreach ($this->rules['templates'] as $rule) {
            if (isset($rule['from']) && $rule['from'] < $seconds) {
                continue;
            }
            if (isset($rule['till']) && $rule['till'] >= $seconds) {
                continue;
            }
            if (isset($rule['calendar']) && $rule['calendar'] != $calendar) {
                continue;
            }
            return $rule;
        }
        return $this->rules['default'];
    }

    private function getParam($key) {
        return $this->params[$key];
    }

    private function getCountWithUnit($count, $form) {
        if ($count === 0) return "";
        $c10 = $count %10;
        $c100 = $count %100;
        if ($c10 == 1 && $c100 != 11) {
            return str_replace("%cnt", $count, $form[0]);
        }
        if ($c10 >= 2 && $c10 <= 4 && ($c100 < 10 || $c100 > 20)) {
            return str_replace("%cnt", $count, $form[1]);
        }
        return str_replace("%cnt", $count, $form[2]);
    }
}
