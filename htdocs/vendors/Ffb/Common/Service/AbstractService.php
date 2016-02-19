<?php

namespace Ffb\Common\Service;

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
 * @author marcus.gnass
 */
abstract class AbstractService {

    /**
     * Service Locator
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $_sl;

    /**
     *
     * @var \Zend\Log\Logger
     */
    protected $_logger;

    /**
     *
     * @var bool
     */
    protected $_isLoggerActive = false;

    /**
     *
     * @var \Zend\Mvc\Controller\Plugin\FlashMessenger
     */
    protected $_flashMessenger;

    /**
     *
     * @var \Ffb\Common\I18n\Translator\Translator
     */
    protected $_translator;

    /**
     * User
     *
     * @var UserEntity
     */
    protected $_user;

    /**
     *
     * @var \Zend\Mvc\Controller\Plugin\Url
     */
    protected $_url;

    /**
     *
     * @var \Zend\Http\Request
     */
    protected $_request;

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param UserEntity $user (optional) entity of user
     */
    public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $sl, $user = null) {
        $this->_sl   = $sl;
        $this->_user = $user;
    }

    /**
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator() {
        return $this->_sl;
    }

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @return \Ffb\Common\Service\AbstractService
     */
    public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $sl) {
        $this->_sl = $sl;
        return $this;
    }

    /**
     *
     * @param \Zend\Log\Logger $logger
     * @return \Ffb\Common\Service\AbstractService
     */
    public function setLogger(\Zend\Log\Logger $logger) {
        $this->_logger = $logger;
        return $this;
    }

    /**
     *
     * @param boolean $isLoggerActive
     * @return \Ffb\Common\Service\AbstractService
     */
    public function setIsLoggerActive($isLoggerActive) {
        $this->_isLoggerActive = $isLoggerActive;
        return $this;
    }

    /**
     *
     * @param string $msg
     */
    public function info($msg) {
        if ($this->_isLoggerActive) {
            $callers = debug_backtrace();
            $caller  = $callers[1];
            $this->_logger->info("[$caller[class]$caller[type]$caller[function]]: " . $msg);
        }
    }

    /**
     * Log exception message and traceback in error log.
     *
     * @param \Exception $e
     */
    protected function _logException(\Exception $e) {
        error_log($e->getMessage());
        error_log($e->getTraceAsString());
    }

    /**
     *
     * @param \Zend\Mvc\Controller\Plugin\FlashMessenger $flashMessenger
     * @return \Ffb\Common\Service\AbstractService
     */
    public function setFlashMessenger(\Zend\Mvc\Controller\Plugin\FlashMessenger $flashMessenger) {
        $this->_flashMessenger = $flashMessenger;
        return $this;
    }

    /**
     *
     * @param $translator
     * @return \Ffb\Common\Service\AbstractService
     */
    public function setTranslator($translator) {
        $this->_translator = $translator;
        return $this;
    }

    /**
     *
     * @return UserEntity
     */
    public function getUser() {
        return $this->_user;
    }

    /**
     *
     * @param UserEntity $user
     * @return \Ffb\Common\Service\AbstractService
     */
    public function setUser($user) {
        $this->_user = $user;
        return $this;
    }

    /**
     * @param \Zend\Mvc\Controller\Plugin\Url $url
     * @return \Ffb\Common\Service\AbstractService
     */
    public function setUrl(\Zend\Mvc\Controller\Plugin\Url $url) {
        $this->_url = $url;
        return $this;
    }

    /**
     *
     * @return \Zend\Http\Request
     */
    public function getRequest() {
        return $this->_request;
    }

    /**
     *
     * @param \Zend\Http\Request $request
     * @return \Ffb\Common\Service\AbstractService
     */
    public function setRequest(\Zend\Http\Request $request) {
        $this->_request = $request;
        return $this;
    }

    /**
     *
     * @param \Zend\View\Model\ViewModel $view
     * @return array
     */
    protected function _getViewVarsAsArray(\Zend\View\Model\ViewModel $view) {

        // get variables (array | \ArrayAccess | \Traversable)
        $variables = $view->getVariables();

        // transform object to plain array
        if (is_object($variables)) {
            $variables = $variables->getArrayCopy();
        }

        // Set flash messages to json
        $messenger = new \Zend\Mvc\Controller\Plugin\FlashMessenger();
        $messages  = $messenger->getCurrentMessages();
        if (count($messages) > 0) {
            $variables['messages'] = $messenger->getCurrentMessages();
        }

        // return plain array
        return $variables;
    }

    /**
     * Handle an exception.
     *
     * @param \Exception $e to handle
     * @param string $message [optional] to display if none is given the exceptions message will be displayed
     * @return array
     */
    protected function _handleException(
        \Exception $e,
        $message = null
    ) {

        // log exception
        error_log($e->getMessage());
        error_log($e->getTraceAsString());

        // determine message to display (if none has been given)
        if (!$message) {
            if (   $e instanceof \Doctrine\DBAL\DBALException
                || $e instanceof \Doctrine\ORM\Query\QueryException
            ) {

                // translate message if possible
                $message = $this->_translator->translate('ERR_DATABASE_ABSTRACTION_LAYER');
            } else if ($e instanceof \Ffb\Common\Service\Exception\MaxFileSizeException) {

                // translate message if possible
                $message = sprintf($this->_translator->translate($e->getMessage()), number_format($e->getCode() / 1000000));
            } else {

                // translate message if possible
                $message = $this->_translator->translate($e->getMessage());
            }
        } else {

            // translate message if possible
            $message = $this->_translator->translate($message);
        }

        $result = array(
            'state'    => 'error',
            'messages' => array(
                $message
            )
        );

        if (   $e instanceof \Ffb\Eventus\Exception\InvalidFormException
            && is_array($e->invalidFields)
            && count($e->invalidFields) > 0
        ) {
            $result['invalidFields'] = $e->invalidFields;
        }

        return $result;
    }
}
