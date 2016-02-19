<?php

namespace Ffb\Common\I18n\Translator;

/**
 *
 * @author unknown
 */
class Translator {

    /**
     * Translator
     *
     * @var \Ffb\Common\I18n\Translator\Translator
     */
    private static $_instance;

    /**
     * Translator
     *
     * @var \Zend\Mvc\I18n\Translator
     */
    private static $_translator;

    /**
     * Call this method to get singleton
     *
     * @return \Ffb\Common\I18n\Translator\Translator
     */
    public static function Instance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new static;
        }
        return self::$_instance;
    }

    /**
     * Set translator
     *
     * @param \Zend\Mvc\I18n\Translator $translator
     */
    public static function setTranslator(\Zend\Mvc\I18n\Translator $translator) {
        self::$_translator = $translator;
    }

    /**
     * Get translator
     *
     * @return \Zend\Mvc\I18n\Translator
     */
    public static function getTranslator() {
        return self::$_translator;
    }

    /**
     * Translate a message.
     *
     * @param  string $message
     * @param  string $textDomain
     * @param  string $locale
     * @return string
     */
    public static function translate($message, $textDomain = 'default', $locale = null) {
        return self::$_translator->translate($message, $textDomain, $locale);
    }
    
    /**
     * Shortcut for Translate a message.
     *
     * @param  string $message
     * @param  string $textDomain
     * @param  string $locale
     * @return string
     */
    public static function t($message, $textDomain = 'default', $locale = null) {
        return self::translate($message, $textDomain, $locale);
    }
    
    /**
     * Shortcut for get Locale
     * 
     * @return string
     */
    public static function getLocale() {
        return self::$_translator->getTranslator()->getLocale();
    }

}
