<?php

namespace Ffb\Frontend\View\Helper;

use Ffb\Common\DateFormatter\DateFormatter;

/**
 * Helper for displaying formatted dates.
 */
class HtmlDateFormatHelper extends AbstractBackendHtmlElement {

    /**
     * Generates a date output html by invoke
     *
     * @param string|\DateTime $date to format
     * @param string $format to use (optional)
     * @param string $locale to use (optional)
     * @return string formatted date
     */
    public function __invoke($date, $format = null, $locale = null) {

        return $this->getHtml($date, $format, $locale);
    }

    /**
     * Generates date output
     *
     * @param string|\DateTime $date to format
     * @param string $format to use (optional)
     * @param string $locale to use (optional)
     * @return string formatted date
     */
    public function getHtml($date, $format = null, $locale = null) {

        return DateFormatter::getInstance()->format($date, $format, $locale);
    }
}
