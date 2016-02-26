<?php

namespace Ffb\Backend\Model;

use Ffb\Backend\Entity;

use Zend\ServiceManager;

/**
 *
 * @author erdal.mersinlioglu
 */
class LangModel extends AbstractBaseModel {

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param \Zend\Log\Logger $logger
     * @param Entity\UserEntity $identity
     */
    public function __construct(
            ServiceManager\ServiceLocatorInterface $sl,
            \Zend\Log\Logger $logger = null,
            Entity\UserEntity $identity = null) {
        $this->_entityClass = 'Ffb\Backend\Entity\LangEntity';
        parent::__construct($sl, $logger, $identity);
    }

    /**
     * Get current language from session.
     */
    public static function getCurrentLanguage() {
        $session = new \Zend\Session\Container('default');
        return $session->offsetGet('languageCode');
    }

    /**
     * Returns the active languages
     * @return array
     */
    public function getActiveLanguages() {

        return $this->findBy(array('isActive' => true), array('sort' => 'ASC'));
    }

    /**
     * Returns the active languages as array
     *
     * $result = array(
     *      'langId' => 'iso'
     * )
     *
     * @return array
     */
    public function getActiveLanguagesAsArray() {
        $result = array();
        foreach ($this->getActiveLanguages() as $lang) {
            $result[$lang->getId()] = $lang->getIso();
        }
        return $result;
    }

}