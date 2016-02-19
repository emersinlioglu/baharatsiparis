<?php

namespace Ffb\Common\View\Helper;

use Zend\View\Helper;

/**
 * Class AbstractHtmlElement
 * @package Ffb\Common\View\Helper
 */
abstract class AbstractHtmlElement extends Helper\AbstractHtmlElement {

    /**
     * List of translations
     * @var array
     */
    protected $_translations = array();

    /**
     * Converts an associative array into a string of tag attributes.
     *
     * @param array $attribs
     *     from this array, each key-value pair is converted to an attribute name and value
     * @return string
     *     The XHTML for the attributes.
     */
    protected function htmlAttribs($attribs) {

        $view = $this->getView();
        if ($view) {
            $escaper = $view->plugin('escapehtml');
        } else {
            $escaper = new Helper\EscapeHtml();
        }

        $xhtml   = '';
        foreach ((array) $attribs as $key => $val) {

            $key = $escaper($key);
            if (('on' == substr($key, 0, 2)) || ('constraints' == $key)) {
                // Don't escape event attributes; _do_ substitute double quotes with singles
                if (!is_scalar($val)) {
                    // non-scalar data should be cast to JSON first
                    $val = \Zend\Json\Json::encode($val);
                }
                // Escape single quotes inside event attribute values.
                // This will create html, where the attribute value has
                // single quotes around it, and escaped single quotes or
                // non-escaped double quotes inside of it
                $val = str_replace('\'', '&#39;', $val);
            } else {
                if (is_array($val)) {
                    $val = implode(' ', $val);
                }
                $key = $escaper($key);
            }

            if ('id' == $key) {
                $val = $this->normalizeId($val);
            }

            if (strpos($val, '"') !== false) {
                $xhtml .= " $key='$val'";
            } else {
                $xhtml .= " $key=\"$val\"";
            }
        }

        return $xhtml;
    }

    /**
     * get Zend Escaper instance
     * @return null|Escaper\Escaper
     */
    protected function getEscaper() {

        $view = $this->getView();
        if ($view) {
            $escaper = $view->plugin('escapehtml');
        } else {
            $escaper = new Helper\EscapeHtml();
        }

        return $escaper->getEscaper();
    }

    /**
     * Translations strings setter
     * @param array $translations
     */
    public function setTranslations(array $translations) {
        $this->_translations = $translations;
    }

    /**
     * Translations strings getter
     * @return array
     */
    public function getTranslations() {
        return $this->_translations;
    }

    /**
     * Returns a traslation string
     * @param string $message
     * @return string
     */
    protected function getTranslation($message) {

        if (isset($this->_translations[$message])) {
            return $this->_translations[$message];
        } else {
            return $message;
        }
    }

}
