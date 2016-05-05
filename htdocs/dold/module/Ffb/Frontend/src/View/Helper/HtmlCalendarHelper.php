<?php //

namespace Ffb\Frontend\View\Helper;

use Ffb\Backend\Entity;

use Zend\Form\View\Helper;

/**
 *
 * @author marcus.gnass
 */
class HtmlCalendarHelper extends Helper\AbstractHelper {

    /**
     *
     * @param {\DateTime|string} $from
     * @param {\DateTime|string} $until
     * @param int $monthCount
     * @return string
     */
    public function __invoke($from, $until, $monthCount) {
        return $this->getHtml($from, $until, $monthCount);
    }

    /**
     *
     * @param {\DateTime|string} $from
     * @param {\DateTime|string} $until
     * @param integer $monthCount
     * @return string
     */
    public function getHtml($from, $until, $monthCount) {

        if (is_string($from)) {
            $from = new \DateTime($from);
        }
        if (is_string($until)) {
            $until = new \DateTime($until);
        }

        $out = '';
        for ($i = 0; $i < $monthCount; $i++) {
            $month = clone $from;
            $month->add(new \DateInterval('P' . strval($i) . 'M'));
            $month->setTime(0, 0, 0);
            $out .= $this->_getMonth($month, $from, $until);
        }

        return $out;
    }

    /**
     *
     * @param \DateTime $date
     * @param \DateTime $from
     * @param \DateTime $until
     * @return string
     */
    private function _getMonth(\DateTime $date, \DateTime $from, \DateTime $until) {

        $weekdays = array(
            'Mo' => 'Montag',
            'Di' => 'Dienstag',
            'Mi' => 'Mittwoch',
            'Do' => 'Donnerstag',
            'Fr' => 'Freitag',
            'Sa' => 'Samstag',
            'So' => 'Sonntag'
        );

        // build table header
        $thead = '';
        foreach ($weekdays as $abbr => $weekday) {
            $thead .= '<th><abbr title="' . $weekday . '">' . $abbr . '</abbr></th>';
        }
        $thead = '<thead><tr>' . $thead . '</tr></thead>';

        $month = (int) $date->format('m');
        $year = (int) $date->format('Y');
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // determine first day
        $firstDay = \DateTime::createFromFormat('Y-m-d', "$year-$month-1");
        $firstDay->sub(new \DateInterval('P' . strval((int) $firstDay->format('N') - 1) . 'D'));
        $firstDay->setTime(0, 0, 0);

        // determine last day
        $lastDay = \DateTime::createFromFormat('Y-m-d', "$year-$month-$daysInMonth");
        $lastDay->add(new \DateInterval('P' . strval(8 - (int) $lastDay->format('N')) . 'D'));
        $lastDay->setTime(0, 0, 0);

        $period = new \DatePeriod($firstDay, new \DateInterval('P1D'), $lastDay);

        $tbody = '';
        foreach ($period as $day) {
            if (1 === (int) $day->format('N')) {
                if (0 < strlen(trim($tbody))) $tbody .= '</tr>';
                $tbody .= '<tr>';
            }
            if ($month === (int) $day->format('m')) {
                $tbody .= '<td data-date="' . $day->format('Y-m-d') . '"';
                if ($from <= $day && $day <= $until) {
                    $tbody .= ' class="fair';
                    if ($day == $from) $tbody .= ' arrival';
                    if ($day == $until) $tbody .= ' departure';
                    $tbody .= '"';
                }
                $tbody .= '>' . $day->format('d') . '</td>';
            } else {
                $tbody .= '<td>&nbsp;</td>';
            }
        }

        $tbody = '<tbody>' . $tbody . '</tr></tbody>';

        $headline = '<h3>' . $date->format('F Y') . '</h3>';

        $table = '<table>' . $thead . $tbody . '</table>';

        $out = '<div class="calendar">' . $headline . $table . '</div>';

        return $out;
    }
}
