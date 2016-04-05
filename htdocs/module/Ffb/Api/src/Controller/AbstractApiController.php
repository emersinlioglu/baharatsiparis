<?php

namespace Ffb\Api\Controller;

use Ffb\Common\DateFormatter;
use Ffb\Common\I18n\Translator;

use Ffb\Backend\Entity;

use Zend\Authentication;
use Zend\Http\Response;
use Zend\Json\Json;
use Zend\Log;
use Zend\Mvc;
use Zend\Permissions\Acl;
use Zend\View\Model;

/**
 *
 * @author erdal.mersinlioglu
 */
abstract class AbstractApiController extends Mvc\Controller\AbstractRestfulController {

//    /**
//     * access control list
//     *
//     * @var Acl\Acl
//     */
//    public $acl;
//
//    /**
//     * Authentication
//     *
//     * @var \Zend\Authentication\AuthenticationService
//     */
//    public $auth;

//    protected $eventIdentifier = 'Ffb\Api\AbstractApiController';

    /**
     * Config
     *
     * @var array
     */
    public $config;

    /**
     * Logger
     *
     * @var \Zend\Log\Logger
     */
    public $logger;

    /**
     * Translator
     *
     * @var \Ffb\Common\I18n\Translator\Translator
     */
    public $translator;

    /**
     * DateFormatter
     *
     * @var \Ffb\Common\DateFormatter\DateFormatter
     */
    public $formatter;

    /**
     * Is Ajax request
     *
     * @var boolean
     */
    protected $_isAjax;

    /**
     * Master language code
     * @var string
     */
    protected $_masterLang = '';

    /**
     * Create Controller
     */
    public function __construct() {}

    /**
     * Abstract function for preDispatch
     */
    public function preDispatch() {

        $this->_isAjax = $this->getRequest()->isXmlHttpRequest();
    }

    /**
     * Attach Controller listeners
     *
     * @see \Zend\Mvc\Controller\AbstractController::attachDefaultListeners()
     */
    public function attachDefaultListeners() {
        parent::attachDefaultListeners();

        $sl = $this->getServiceLocator();
        $em = $this->getEventManager();

        // Set config
        $this->config = array(
            'application' => $sl->get('Application\Config'),
            'module'      => $sl->get('Config')
        );

        // masterLang
        $this->_masterLang = $this->config['module']['translator']['master_language_code'];

        // Set Translator
        $translator = Translator\Translator::Instance();
        $zfTranslator = $sl->get('translator');
        $translator->setTranslator($zfTranslator);
        $this->translator = $translator;

        // Set Date Formatter
        $this->formatter = DateFormatter\DateFormatter::getInstance();

        // Add set Logger to dispatch.pre
        $eventManager = $this->getEventManager();
        $eventManager->attach(Mvc\MvcEvent::EVENT_DISPATCH, array($this, 'checkAuthentication'), 2);
        $eventManager->attach(Mvc\MvcEvent::EVENT_DISPATCH, array($this, 'preDispatch'), 2);
        $eventManager->attach(Mvc\MvcEvent::EVENT_DISPATCH, array($this, 'setLogger'), 2);
    }

    /**
     * Set controller logger
     *
     * @param \Zend\Mvc\MvcEvent $e Optional event object from EventManager
     * @param array $config
     */
    public function setLogger(Mvc\MvcEvent $e = null, $config = null) {

        if (!$config) {
            $config = $this->config['application'];
        }

        $this->logger = new Log\Logger();

        if (isset($config['application']['app_log_file'])) {
            $writer = new Log\Writer\Stream($config['application']['app_log_file']);
        } else {
            $writer = new \Zend\Log\Writer\Null();
        }

        $this->logger->addWriter($writer);
    }

    /**
     * Check authentication.
     * Redirects to user/login if user is not authenticated.
     */
    public function checkAuthentication() {

        // get config & values
        $modConf = $this->getServiceLocator()->get('Config');

        $key = $modConf['rest_api']['key'];

        // get post data
        $data = $this->getRequest()->getPost();

        // check access
        if ($key == $data->get('key')) {
            return true;
        }

        // return 401
        $response = new Response();
        $response->setStatusCode(Response::STATUS_CODE_401);
        $response->setContent(
            Json::encode(array(
                'state' => 'error',
                'code' => Response::STATUS_CODE_401,
                'message' => 'Unauthorized'
            ))
        );
        return $response;
    }

    /**
     * Handle an exception.
     *
     * This method uses a common template for displaying the error message.
     *
     * This method should be used in all controllers.
     *
     * @param \Exception $e
     *         to handle
     * @param \Zend\View\Model\ViewModel $view
     *         to add message to
     * @param string $message [optional]
     *         to display
     *         if none is given the exceptions message will be displayed
     */
    protected function _handleException(
        \Zend\View\Model\ViewModel $view,
        \Exception $e
    ) {

        // log exception
        error_log($e->getMessage());
        error_log($e->getTraceAsString());

        // set message to view model
        $view->setTemplate('error/index');
        $view->setVariable('state', 'error');
        $view->setVariable('code', $e->getCode());
        $view->setVariable('message', $e->getMessage());
    }

    /**
     * Master language
     * @return string
     */
    public function getMasterLang() {
        return $this->_masterLang;
    }

}