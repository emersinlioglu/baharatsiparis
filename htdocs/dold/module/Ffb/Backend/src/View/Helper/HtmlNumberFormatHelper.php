<?php

namespace Ffb\Backend\View\Helper;

use Ffb\Common\NumberFormatter\NumberFormatter;

/**
 * Helper for displaying formatted dates.
 */
class HtmlNumberFormatHelper extends AbstractBackendHtmlElement {

    /**
     * Generates a date output html by invoke
     *
     * @param float $number to format
     * @param string $format to use (optional)
     * @param string $locale to use (optional)
     * @return string formatted date
     */
    public function __invoke($number, $format = null, $locale = null) {

        return $this->getHtml($number, $format, $locale);
    }

    /**
     * Generates date output
     *
     * @param float $number to format
     * @param string $format to use (optional)
     * @param string $locale to use (optional)
     * @return string formatted date
     */
    public function getHtml($number, $format = null, $locale = null) {

        return NumberFormatter::getInstance()->format($number, $format, $locale);
    }
}
