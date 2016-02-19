<?php

/**
 * Zend Framework (http://framework.zend.com/)
 */

namespace Ffb\Common\Validator;

/**
 */
class PositiveOnly extends \Zend\Validator\AbstractValidator {

    const INVALID = 'positiveInvalid';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID => "The input must been positive",
    );

    /**
     * Returns true if and only if $value only contains digit characters
     *
     * @param  string $value
     * @return bool
     */
    public function isValid($value) {

        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue((string) $value);

        if (0 > (float) $this->getValue()) {
            $this->error(self::INVALID);
            return false;
        }

        return true;

    }

}
