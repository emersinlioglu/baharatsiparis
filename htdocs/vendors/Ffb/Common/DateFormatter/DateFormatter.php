<?php

namespace Ffb\Common\DateFormatter;

/**
 * DateFormatter class
 *
 * This class is implemented as singleton.
 */
class DateFormatter {

    /**
     * @var string
     */
    const LOCALE_DEFAULT = 'de';

    /**
     * Formats
     *
     * @see http://php.net/manual/de/function.date.php
     */
    private static $_formats = array(
        'en' => array(
            'dayMonthDot'   => 'm/d',
            'default'       => 'm/d/Y',
            'long'          => 'm/d/Y H:i:s',
            'middle'        => 'm/d/y',
            'short'         => 'm/d/y',
            'weekday'       => 'l',
            'shortWeekday'  => 'D',
            'day'           => 'd',
            'standard'      => 'Y-m-d',
            'time'          => 'H:i:s'
        ),
        'de' => array(
            'dayMonthDot'   => 'd.m.',
            'default'       => 'd.m.Y',
            'long'          => 'd.m.Y, H:i \U\h\r',
            'middle'        => 'd.m.y',
            'short'         => 'd.m.y',
            'weekday'       => 'l',
            'shortWeekday'  => 'D',
            'day'           => 'd',
            'standard'      => 'Y-m-d',
            'time'          => 'H:i \U\h\r'
        )
    );

    /**
     * Translations
     */
    private static $_trans = array(
        'Monday'    => 'Montag',
        'Tuesday'   => 'Dienstag',
        'Wednesday' => 'Mittwoch',
        'Thursday'  => 'Donnerstag',
        'Friday'    => 'Freitag',
        'Saturday'  => 'Samstag',
        'Sunday'    => 'Sonntag',
        'Mon'       => 'Mo',
        'Tue'       => 'Di',
        'Wed'       => 'Mi',
        'Thu'       => 'Do',
        'Fri'       => 'Fr',
        'Sat'       => 'Sa',
        'Sun'       => 'So',
        'January'   => 'Januar',
        'February'  => 'Februar',
        'March'     => 'März',
        'May'       => 'Mai',
        'June'      => 'Juni',
        'July'      => 'Juli',
        'October'   => 'Oktober',
        'December'  => 'Dezember'
    );

    /**
     *
     * @var DateFormatter
     */
    private static $_instance = null;

    /**
     * private constructor
     *
     */
    public function __construct() {}

    /**
     * private clone method
     *
     */
    public function __clone() {}

    /**
     * call this method to get singleton
     *
     * @return DateFormatter
     */
    public static function getInstance() {

        if (self::$_instance === null) {
            self::$_instance = new DateFormatter();
        }

        return self::$_instance;
    }

    /**
     *
     * @param string $locale to use (optional)
     * @param string $format to use (optional)
     * @return array
     */
    public function getFormat($locale = null, $format = null) {

        if (empty($locale) || !isset(self::$_formats[$locale])) {
            $locale = self::LOCALE_DEFAULT;
        }

        if (empty($format) || !isset(self::$_formats[$locale][$format])) {
            $format = 'default';
        }

        return self::$_formats[$locale][$format];
    }

    /**
     * @param string|\DateTime $date to format
     * @param string $format to use (optional)
     * @param string $locale to use (optional)
     * @return string formatted date
     */
    public function format($date, $format = null, $locale = null) {

        // check empty value
        if (empty($date)) {
            return null;
        }

        //create date from value, return null
        if (!$date instanceof \DateTime) {
            $date = new \DateTime($date);
        }

        if (empty($locale) || !isset(self::$_formats[$locale])) {
            $locale = self::LOCALE_DEFAULT;
        }

        if (empty($format) || !isset(self::$_formats[$locale][$format])) {
            $format = 'default';
        }

        $result = strtr($date->format(self::$_formats[$locale][$format]), self::$_trans);
        return $result;
    }
}
