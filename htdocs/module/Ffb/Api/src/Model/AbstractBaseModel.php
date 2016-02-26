<?php

namespace Ffb\Api\Model;


use Ffb\Backend\Entity;
use Ffb\Common\Entity\AbstractTranslatableEntity;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractBaseModel extends \Ffb\Backend\Model\AbstractBaseModel {

    /**
     * Master language
     * @var string
     */
    protected $_masterLanguage;

    /**
     * Hostname with schema
     * @var string
     */
    protected $_host;

    /**
     * AbstractBaseModel constructor.
     * @param ServiceLocatorInterface $sl
     * @param \Zend\Log\Logger $logger
     * @param Entity\UserEntity $identity
     */
    public function __construct(ServiceLocatorInterface $sl, \Zend\Log\Logger $logger = null, Entity\UserEntity $identity = null) {
        parent::__construct($sl, $logger, $identity);

        $modConfig = $this->_sl->get('Config');

        $this->_masterLanguage = $modConfig['translator']['master_language_code'];
    }

    /**
     * @return string
     */
    public function getMasterLanguage() {
        return $this->_masterLanguage;
    }

    /**
     * @return string
     */
    public function getHost() {
        return $this->_host;
    }

    /**
     * @param string $host
     */
    public function setHost($host) {
        $this->_host = $host;
    }

}