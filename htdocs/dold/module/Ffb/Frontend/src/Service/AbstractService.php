<?php

namespace Ffb\Frontend\Service;

use \Ffb\Backend\Entity;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Base class for all services.
 *
 * Debugging services:
 * In order to debug a service you can attach a logger to it, activate and
 * eventually use it.
 * $this->setLogger();
 * $this->setIsLoggerActive(true);
 * $this->info('foobar');
 *
 * @author murat.purc
 * @author
 */
abstract class AbstractService extends \Ffb\Common\Service\AbstractService {

    /**
     * User
     *
     * @var Entity\UserEntity
     */
    protected $_user;

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param UserEntity $user (optional) entity of user
     */
    public function __construct(ServiceLocatorInterface $sl, Entity\UserEntity $user = null) {
        parent::__construct($sl, $user);
    }
}
