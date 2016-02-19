<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Ffb\Common\Filter;

class Null extends \Zend\Filter\Null
{
   
    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * Returns null representation of $value, if value is empty and matches
     * types that should be considered null.
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        $type = $this->getType();

        // FLOAT (0.0)
//        if ($type >= self::TYPE_FLOAT) {
//            $type -= self::TYPE_FLOAT;
//            if (is_float($value) && ($value == 0.0)) {
//                return null;
//            }
//        }

        // STRING ZERO ('0')
//        if ($type >= self::TYPE_ZERO_STRING) {
//            $type -= self::TYPE_ZERO_STRING;
//            if (is_string($value) && ($value == '0')) {
//                return null;
//            }
//        }

        // STRING ('')
        if ($type >= self::TYPE_STRING) {
            $type -= self::TYPE_STRING;
            if (is_string($value) && ($value == '')) {
                return null;
            }
        }

        // EMPTY_ARRAY (array())
        if ($type >= self::TYPE_EMPTY_ARRAY) {
            $type -= self::TYPE_EMPTY_ARRAY;
            if (is_array($value) && ($value == array())) {
                return null;
            }
        }

        // INTEGER (0)
//        if ($type >= self::TYPE_INTEGER) {
//            $type -= self::TYPE_INTEGER;
//            if (is_int($value) && ($value == 0)) {
//                return null;
//            }
//        }

        // BOOLEAN (false)
//        if ($type >= self::TYPE_BOOLEAN) {
//            $type -= self::TYPE_BOOLEAN;
//            if (is_bool($value) && ($value == false)) {
//                return null;
//            }
//        }

        return $value;
    }
}
