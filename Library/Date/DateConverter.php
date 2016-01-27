<?php

namespace Library\Date;

use DateTimeZone;

class DateConverter extends \DateTime {
    private $time;

    public function __construct($time = 'now', DateTimeZone $timezone = null) {
        parent::__construct($time, $timezone);
        $this->processor = new Processor();
    }

    public function setHumanTemplate(array $rules) {
        $this->processor->initRules($rules);
    }

    /**
     * @param DateConverter | null $from is need for get difference from other datetime than now, by default null mean now
     * @param bool $relative if true add ago or later or after or before
     * @return string
     */
    public function getHumanString(DateConverter $from = null, $relative = true) {
        $is_now = ($from === null);
        if ($is_now) {
            $from = new DateConverter();
        }
        $interval = $this->diff($from);
        $units = [];
        $units['%year'] = $interval->y;
        $units['%month'] = $interval->m;
        $units['%day'] = $interval->d;
        $units['%hour'] = $interval->h;
        $units['%minute'] = $interval->i;
        $units['%second'] = $interval->s;
        $is_future = ($interval->invert == 1);
        if ($is_now) {
            $key = $is_future ? 'later' : 'ago';
        } else {
            $key = $is_future ? 'after' : 'before';
        }
        if (!$relative) {
            $key = false;
        }
        return $this->processor->translate($this, $key, $units, $this->getTimestamp() - $from->getTimestamp());
    }
}
