<?php

namespace Ffb\Common\Form\View\Helper;

use Ffb\Common\DateFormatter\DateFormatter;

use Zend\Form\ElementInterface;

/**
 * Helper for displaying localized & formatted dates in a formfield.
 *
 * @author
 */
class FormDateFormatted extends \Zend\Form\View\Helper\FormText {

    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param ElementInterface|null $element
     * @param string $format
     * @param string $locale
     * @return string FormInput
     */
    public function __invoke(ElementInterface $element = null, $format = null, $locale = null) {

        if ($element) {
            try {
                $date = $element->getValue();
                $date = DateFormatter::getInstance()->format($date, $format, $locale);
                $element->setValue($date);
            } catch (\Exception $e) {
                // if the date cannot be formatted properly leave it as it is
            }
        }

        return parent::render($element);
    }
}
