<?php

namespace Ffb\Common\Form\View\Helper;

use Ffb\Common\NumberFormatter\NumberFormatter;
use Zend\Form\ElementInterface;

/**
 * Helper for displaying localized & formatted numbers in a formfield.
 * ilja.schwarz
 */
class FormNumberFormatted extends \Zend\Form\View\Helper\FormText {

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
    public function __invoke(ElementInterface $element = null, $format = 'decimal', $locale = null) {

        if ($element) {
            try {

                $value = $element->getValue();                
                $value = NumberFormatter::getInstance()->format($value, $format, $locale);
                $element->setValue($value);
            } catch (\Exception $e) {
                // if the date cannot be formatted properly leave it as it is
            }
        }

        return parent::render($element);
    }
}
