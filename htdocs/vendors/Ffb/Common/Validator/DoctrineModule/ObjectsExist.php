<?php
/*
 * Modified version of Doctrine's ObjectExists filter
 * Purpose is to allow using that filter from within a multiple select element
 */

namespace Ffb\Common\Validator\DoctrineModule;

use Zend\Validator\Exception;
use Zend\Stdlib\ArrayUtils;

/**
 * Class that validates if objects exist in a given repository with a given list of matched fields
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.4.0
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ObjectsExist extends \DoctrineModule\Validator\ObjectExists
{
    /**
     * @param string|array $value a field value or an array of field values if more fields have been configured to be
     *                      matched
     * @return array
     * @throws \Zend\Validator\Exception\RuntimeException
     */
    protected function cleanSearchValues($value)
    {
        $value = (array) $value;

        if (ArrayUtils::isHashTable($value)) {
            $matchedFieldsValues = array();

            foreach ($this->fields as $field) {
                if (!array_key_exists($field, $value)) {
                    throw new Exception\RuntimeException(
                        sprintf(
                            'Field "%s" was not provided, but was expected since the configured field lists needs'
                            . ' it for validation',
                            $field
                        )
                    );
                }

                $matchedFieldsValues[$field] = $value[$field];
            }
        } else {
            $arrValues = array_values($this->fields);

            $matchedFieldsValues = array();
            foreach ($value as $entry) {
                $matchedFieldsValues[$arrValues[0]] = $entry;
            }
        }

        return $matchedFieldsValues;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid($values)
    {
        $values = $this->cleanSearchValues($values);
        $matches = $this->objectRepository->findBy($values);


        $matchObj = array();
        if ($matches) {
            foreach ($matches as $match) {

                if (is_object($match)) {
                    $matchObj[] = $match;
                }
            }
        }

        if (0 < count($matches) && count($matches) === count($matchObj)) {
            return true;
        }

        $this->error(self::ERROR_NO_OBJECT_FOUND, $values);

        return false;
    }
}
