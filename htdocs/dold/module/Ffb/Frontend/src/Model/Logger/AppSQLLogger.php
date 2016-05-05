<?php
namespace Ffb\Frontend\Model\Logger;

use Ffb\Common\Entity as CommonEntity;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Log\Logger;

class AppSQLLogger implements \Doctrine\DBAL\Logging\SQLLogger {

    protected $_logger;

    /**
     *
     * @param type $logger
     */
    public function __construct($logger) {

        $this->_logger = $logger;
    }

    /**
     *
     * @param type $sql
     * @param array $params
     * @param array $types
     */
    public function startQuery($sql, array $params = null, array $types = null) {
        $this->_logger->info($sql);
        if (!empty($params)) {
            $this->_logger->info(print_r($params,1));
        }
        if (!empty($types)) {
            $this->_logger->info(print_r($types,1));
        }
    }

    /**
     * 
     */
    public function stopQuery() {}
}