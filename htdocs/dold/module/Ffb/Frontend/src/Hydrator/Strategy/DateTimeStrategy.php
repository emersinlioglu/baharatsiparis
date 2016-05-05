<?php

namespace Ffb\Frontend\Hydrator\Strategy;

use DateTime;
use Zend\Stdlib\Hydrator\Strategy\DefaultStrategy;

/**
 *
 * @author erdal.mersinlioglu
 */
class DateTimeStrategy extends DefaultStrategy {

    /**
     * {@inheritdoc}
     *
     * Convert a string value into a DateTime object
     */
    public function hydrate($value) {

        if (is_string($value) && '' === $value) {

            $value = null;
        } else if (is_string($value)) {

            $value = new DateTime($value);
        }

        return $value;
    }
}