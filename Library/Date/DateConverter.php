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
    public function getHumanString(DateConverter $from = null, $relative = false) {
        $is_now = ($from === null);
        if ($is_now) {
            $from = new DateConverter();
        }
        $ts = $this->getTimestamp();
        $calendar_diff = (int)($ts - mktime(0, 0, 0)) / 86400;
        if ($calendar_diff < -6) {
            $calendar = 'default';
        } else if ($calendar_diff < -1) {
            $calendar = 'lastWeek';
        } else if ($calendar_diff < 0) {
            $calendar = 'lastDay';
        } else if ($calendar_diff < 1) {
            $calendar = 'sameDay';
        } else if ($calendar_diff < 2) {
            $calendar = 'nextDay';
        } else if ($calendar_diff < 7) {
            $calendar = 'nextWeek';
        } else {
            $calendar = 'default';
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
        return $this->processor->translate($this, $key, $units, $ts - $from->getTimestamp(), $calendar);
    }
}
