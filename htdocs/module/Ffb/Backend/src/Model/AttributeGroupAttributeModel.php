<?php

namespace Ffb\Backend\Model;

use Ffb\Backend\Entity;

use Zend\ServiceManager;

/**
 * @author erdal.mersinlioglu
 */
class AttributeGroupAttributeModel extends AbstractBaseModel {

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param \Zend\Log\Logger $logger
     * @param Entity\UserEntity $identity
     */
    public function __construct(
            ServiceManager\ServiceLocatorInterface $sl,
            \Zend\Log\Logger $logger = null,
            Entity\UserEntity $identity = null) {
        $this->_entityClass = 'Ffb\Backend\Entity\AttributeGroupAttributeEntity';
        parent::__construct($sl, $logger, $identity);
    }

}