<?php

namespace Ffb\Common\NumberFormatter;

/**
 * NumberFormatter class
 */
class NumberFormatter {

    /**
     * @var string
     */
    const LOCALE_DEFAULT = 'de_DE';

    /**
     * @var array
     */
    private static $_locales = array(
        'de' => 'de_DE',
        'en' => 'en_US'
    );

    /**
     * @var NumberFormatter
     */
    private static $_instance = null;

    /**
     * private constructor
     */
    private function __construct() {}

    /**
     * private clone method
     */
    private function __clone() {}

    /**
     * call this method to get singleton
     *
     * @return NumberFormatter
     */
    public static function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new NumberFormatter();
        }
        return self::$_instance;
    }


    /**
     * @param number $number
     * @param string $format
     * @param string $locale
     * @param array $opt
     * @return string
     */
    public function format($number, $format = 'default', $locale = null, array $opt = array()) {

        // check value exist
        if (!is_string($number) && !is_int($number) && !is_float($number)) {
            return null;
        }

        // check empty string
        if (is_string($number) && $number === '') {
            return null;
        }

        if (empty($locale) || !isset(self::$_locales[$locale])) {
            $locale = self::LOCALE_DEFAULT;
        } else {
            $locale = self::$_locales[$locale];
        }

        // parse format
        switch ($format) {
            case 'currency':
                $style = \NumberFormatter::CURRENCY;
                break;
            case 'percent':
                $style = \NumberFormatter::PERCENT;
                break;
            case 'scientific':
                $style = \NumberFormatter::SCIENTIFIC;
                break;
            case 'decimal':
                $style = \NumberFormatter::DECIMAL;
                break;
            case 'default':
            default:
                $style = \NumberFormatter::TYPE_DEFAULT;
                $opt[\NumberFormatter::MIN_INTEGER_DIGITS] = 1;
                break;
        }

        // init formatter
        $formatter = new \NumberFormatter($locale, $style);

        // set attributes
        foreach ($opt as $key => $value) {
            $formatter->setAttribute($key, $value);
        }

        return $formatter->format($number);
    }
}
