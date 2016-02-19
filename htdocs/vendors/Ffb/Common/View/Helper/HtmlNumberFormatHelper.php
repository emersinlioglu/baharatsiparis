<?php

namespace Ffb\Common\View\Helper;

use Ffb\Common\NumberFormatter\NumberFormatter;

/**
 * Helper for displaying formatted dates.
 */
class HtmlNumberFormatHelper extends AbstractHtmlElement {

    /**
     * Generates a date output html by invoke
     *
     * @param float $number to format
     * @param string $format to use (optional)
     * @param string $locale to use (optional)
     * @param array $opt
     * @return string formatted date
     */
    public function __invoke($number, $format = null, $locale = null, array $opt = array()) {

        return $this->getHtml($number, $format, $locale, $opt);
    }

    /**
     * Generates date output
     *
     * @param float $number to format
     * @param string $format to use (optional)
     * @param string $locale to use (optional)
     * @param array $opt
     * @return string formatted date
     */
    public function getHtml($number, $format = null, $locale = null, array $opt = array()) {

        return NumberFormatter::getInstance()->format($number, $format, $locale, $opt);
    }
}
