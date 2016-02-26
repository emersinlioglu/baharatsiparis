<?php

namespace Ffb\Backend\Controller;

use Ffb\Common\DateFormatter;
use Ffb\Common\I18n\Translator;

use Ffb\Backend\Entity;

use Zend\Authentication;
use Zend\Json\Json;
use Zend\Log;
use Zend\Mvc;
use Zend\Permissions\Acl;
use Zend\View\Model;

/**
 *
 * @author erdal.mersinlioglu
 */
abstract class AbstractBackendController extends Mvc\Controller\AbstractActionController {

    /**
     * access control list
     *
     * @var Acl\Acl
     */
    public $acl;

    /**
     * Authentication
     *
     * @var \Zend\Authentication\AuthenticationService
     */
    public $auth;

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
     * Area name for the rights
     * @var string
     */
    protected $_area = '';

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

        // set client settings
        $this->setClientSettings();

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

        // Set acl
        $this->acl = new Acl\Acl();

        // Set Translator
        $translator = Translator\Translator::Instance();
        $zfTranslator = $sl->get('translator');
        $translator->setTranslator($zfTranslator);
        $this->translator = $translator;

        // Set Date Formatter
        $this->formatter = DateFormatter\DateFormatter::getInstance();

        // Add set Logger to dispatch.pre
        $eventManager = $this->getEventManager();
        $eventManager->attach(Mvc\MvcEvent::EVENT_DISPATCH, array($this, 'preDispatch'), 2);
        $eventManager->attach(Mvc\MvcEvent::EVENT_DISPATCH, array($this, 'setLogger'), 2);
        $eventManager->attach(Mvc\MvcEvent::EVENT_DISPATCH, array($this, 'setAuthentication'), 2);
        $eventManager->attach(Mvc\MvcEvent::EVENT_DISPATCH, array($this, 'checkAuthentication'), 2);
        $eventManager->attach(Mvc\MvcEvent::EVENT_DISPATCH, array($this, 'initAcl'), 2);
    }

    /**
     * On Dispatch callBack
     *
     * @param Mvc\MvcEvent $e
     * @return mixed
     */
    public function onDispatch(Mvc\MvcEvent $e) {

        $actionResponse = parent::onDispatch($e);

        // DERTMS-852: Add Application Version Number to title-Tag
        // if user is logged in
        if ($this->auth->hasIdentity()) {
            $this->getServiceLocator()
                ->get('ViewHelperManager')
                ->get('headMeta')
                ->appendName('version', $this->config['application']['application']['app_version']);
        }

        // Check ajax request, return pure json if needed
        if ($this->getRequest()->isXmlHttpRequest()) {

            $accepts = $this->getRequest()->getHeader('Accept')->toString();

            switch (true) {
                case stristr($accepts, 'Accept: application/json'):

                    /* @var $view \Zend\View\Model\ViewModel */
                    $view = $e->getResult();
                    $data = $view->getVariables();

                    // Set flash messages to json
                    $messenger = new \Zend\Mvc\Controller\Plugin\FlashMessenger();
                    $messages  = $messenger->getCurrentMessages();
                    if (count($messages) > 0) {
                        $data['messages'] = $messenger->getCurrentMessages();
                    }

                    // Create JsonModel and update output template to ajax.tpl
                    $jsonView = new Model\JsonModel(array(
                        'json' => Json::encode($data)
                    ));
                    $jsonView->setTemplate('ajax/index');
                    $e->setResult($jsonView);

                    break;

                case stristr($accepts, 'Accept: text/html'):

                    /* @var $view \Zend\View\Model\ViewModel */
                    $view = $e->getResult();
                    $data = $view->getVariables();

                    // Create JsonModel and update output template to ajax.tpl
                    $jsonView = new Model\JsonModel($data);
                    if ($view->getTemplate()) {
                        $jsonView->setTemplate($view->getTemplate());
                    }
                    $e->setResult($jsonView);

                    break;
            }
        }

        return $actionResponse;
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
     * Set controller authentication
     *
     * @param Mvc\MvcEvent|null $e
     * @param Authentication\AuthenticationService|null $auth
     */
    public function setAuthentication(Mvc\MvcEvent $e = null, Authentication\AuthenticationService $auth = null) {

        if ($auth) {
            $this->auth = $auth;
        } else {
            $this->auth = new Authentication\AuthenticationService();
        }
    }

    /**
     * Check authentication.
     * Redirects to user/login if user is not authenticated.
     */
    public function checkAuthentication() {

        if ($this->auth->hasIdentity()) {

            $controllerName = $this->params('controller');
            $controller = "{$controllerName}Controller";

            $hasAccess = $this->checkAccess($this->auth->getIdentity(), $controller::AREA);
            if (!$hasAccess) {
                $mainnaviLinks = $this->_getMainnaviLinks();
                if (count($mainnaviLinks) > 0) {
                    $firstLink = $mainnaviLinks[0];
                    $this->redirect()->toUrl($firstLink['url']);
                }
            }

            return;
        }

        // if no user is logged in and request is xhr, set status code to 500 and
        // set redirect information for login page to response
        if ($this->getRequest()->isXmlHttpRequest()) {
            $ajaxReloginResponse = new \Zend\Http\Response();
            $ajaxReloginResponse->setStatusCode(\Zend\Http\Response::STATUS_CODE_500);
            $ajaxReloginResponse->setContent(
                'redirectto:' . $this->url()->fromRoute('home/default', array(
                        'controller' => 'auth',
                        'action' => 'login'
                    )
                )
            );
            return $ajaxReloginResponse;
        }

        return $this->redirect()->toRoute('home/default', array(
            'controller' => 'auth',
            'action' => 'login'
        ));

    }

    /**
     * Initialization of ACL based on application config file
     */
    public function initAcl() {

        // get config
        $aclConfig = $this->config['application']['acl'];

        // init resources
        foreach ($aclConfig['resources'] as $resource) {
            if (!$this->acl->hasResource($resource)) {
                $this->acl->addResource(new Acl\Resource\GenericResource($resource));
            }
        }

        // init roles
        foreach ($aclConfig['roles'] as $key => $role) {

            $this->acl->addRole($role);

            // init allow actions
            if (array_key_exists('allow', $aclConfig) &&
                array_key_exists($role, $aclConfig['allow'])
            ) {

                foreach ($aclConfig['allow'][$role] as $resource => $actions) {
                    foreach ($actions as $action) {
                        $this->acl->allow($role, $resource, $action);
                    }

                    if ($this->acl->hasResource('mvc:' . $resource)) {
                        $this->acl->allow($role, 'mvc:' . $resource);
                    }
                }
            }

            // init deny actions
            if (array_key_exists('deny', $aclConfig) &&
                array_key_exists($role, $aclConfig['deny'])
            ) {

                foreach ($aclConfig['deny'][$role] as $resource => $actions) {
                    foreach ($actions as $action) {
                        $this->acl->allow($role, $resource, $action);
                    }

                    if ($this->acl->hasResource('mvc:' . $resource)) {
                        $this->acl->allow($role, 'mvc:' . $resource);
                    }
                }
            }
        }

        // set acl to layout
        $this->layout()->setVariable('acl', $this->acl);

        $this->layout()->setVariable('mainnaviLinks', $this->_getMainnaviLinks());

        // set user role to layout
        if ($this->auth && $this->auth->getIdentity()) {

            $this->layout()->setVariables(array(
                'userName'    => $this->auth->getIdentity()->getName(),
                'userLogoutUrl' => $this->url()->fromRoute('home/default', array(
                    'controller' => 'auth',
                    'action' => 'logout'
                )),
                'userRole' => $this->auth->getIdentity()->getRole()
            ));
        }
    }

    public function checkAccess(Entity\UserEntity $user, $area, $function = null, Model\ViewModel $view = null) {

        $userModel = new \Ffb\Backend\Model\UserModel($this->serviceLocator);
        $user = $userModel->findById($user->getId());

        $isAllowed = false;
        switch ($area) {
            case ProductController::AREA:
                    $isAllowed = $user->getAllowProducts();
                break;
            case AttributeController::AREA:
                    $isAllowed = $user->getAllowAttributes();
                break;
            case TemplateController::AREA:
                    $isAllowed = $user->getAllowTemplates();
                break;
            case AdminController::AREA:
                    $isAllowed = $user->getAllowAdmin();
                break;
            case UploadController::AREA:
                    // this area is always allowed
                    $isAllowed = true;
                break;
            default:
                break;
        }

        if (!is_null($function)) {
            switch ($function) {
                case 'delete':
                        $isAllowed &= $user->getAllowDelete();
                    break;
                case 'edit':
                        $isAllowed &= $user->getAllowEdit();
                    break;
                default:
                    break;
            }
        }

        if (!$isAllowed && $view) {
            $view->setVariable('state', 'error');
            $view->setVariable('no_rights', $this->translator->translate('MSG_NO_ACCESS'));
            $view->setTemplate('acl/denied');
            $this->flashMessenger()->addMessage($this->translator->translate('MSG_NO_ACCESS'));
        }

        return $isAllowed;
    }

//    /**
//     * Check access
//     *
//     * @param string $role
//     * @param string $resource
//     * @param string $action
//     * @param \Zend\View\Model\ViewModel $view
//     * @return \Zend\View\Model\ViewModel | boolean
//     */
//    public function checkAccess($role, $resource, $action, Model\ViewModel $view = null) {
//
//        if ($this->acl->isAllowed($role, $resource, $action) || $this->acl->isAllowed($role, $resource, 'all')) {
//
//            // get user model
//            /*$userModel = new \Ffb\Backend\Model\UserModel($this->getServiceLocator());
//
//            // check user access for non sysadmin
//            if ($this->auth->getIdentity()->getRole() !== Entity\UserEntity::USER_ROLE_SYSADMIN) {
//
//
//            }*/
//
//            return true;
//        }
//
//        if ($view) {
//            $view->setVariable('state', 'error');
//            $view->setVariable('no_rights', $this->translator->translate('MSG_NO_ACCESS'));
//            $view->setTemplate('acl/denied');
//            $this->flashMessenger()->addMessage($this->translator->translate('MSG_NO_ACCESS'));
//        }
//
//        return false;
//    }

    /**
     * Set client settings from config
     *
     */
    public function setClientSettings() {

        // get model(s)
        $langModel = new \Ffb\Backend\Model\LangModel($this->getServiceLocator(), $this->logger);
        $languages = $langModel->getActiveLanguages();

        $session = $this->getSession();

        // get config & values
        $dataConf   = $this->config['module']['data_manager'];
        $dataConf['layout'] = array(
            'title' => 'Dold-PIM'
        );
        $layoutConf = $dataConf['layout'];

        // set title
        $this->getServiceLocator()
            ->get('ViewHelperManager')
            ->get('HeadTitle')
            ->append($this->translator->translate($layoutConf['title']))
            ->setSeparator(' - ')
            ->setAutoEscape(false);

        // set backend css
        $this->getServiceLocator()
            ->get('ViewHelperManager')
            ->get('headLink')
            ->prependStylesheet('css/backend/style.css')
            ->prependStylesheet('css/backend/forms.css')
            ->prependStylesheet('css/backend/tables.css');

        //logo
        $logoUrl = null;

        // set layout variables
        $this->layout()->setVariables(array(
            'locale'            => $this->translator->getTranslator()->getLocale(),
            'layoutClass'       => 'dold',
            'layoutClientTitle' => $this->translator->translate($layoutConf['title']),
            'logoUrl'           => $logoUrl,
            'languages'         => $languages,
            'contentLang'       => $session->offsetGet('contentLang') ? $session->offsetGet('contentLang') : Entity\LangEntity::DEFAULT_LANGUAGE_CODE
        ));
    }

    /**
     * Returns the mainnavi links (areas)
     * @return array
     */
    private function _getMainnaviLinks() {

//        default.0.label      = "TTL_PRODUCTS"
//        default.0.route      = "home"
//        default.0.controller = "product"
//        default.0.resource   = "mvc:product"
//        default.0.class      = "first"
//        default.0.pages.0.route      = "home/default"
//        default.0.pages.0.controller = "product"
//
//        default.1.label      = "TTL_ATTRIBUTES_AND_TEMPLATES"
//        default.1.route      = "home/default"
//        default.1.controller = "attribute"
//        default.1.resource   = "mvc:attribute"
//
//        default.2.label      = "TTL_ADMIN"
//        default.2.route      = "home/default"
//        default.2.controller = "admin"
//        default.2.resource   = "mvc:admin"

        $allowedAreas = array();
        $areas = array(
            'products' => array(
                'controller' => 'product',
                'title' => 'TTL_PRODUCTS'
            ),
            'attributes' => array(
                'controller' => 'attribute',
                'title' => 'TTL_ATTRIBUTES_AND_TEMPLATES'
            ),
            'admin' => array(
                'controller' => 'admin',
                'title' => 'TTL_ADMIN'
            ),
        );

        if ($this->auth && $this->auth->hasIdentity()) {

            $actualController = $this->params('controller');
            $identity = $this->auth->getIdentity();

            foreach ($areas as $area => $data) {
                if ($this->checkAccess($identity, $area)) {
                    $allowedAreas[] = array(
                        'title' => $this->translator->translate($data['title']),
                        'url' => $this->url()->fromRoute('home/default', array(
                            'controller' => $data['controller'],
                            'action' => 'index'
                        )),
                        'active' => 0 < stripos($actualController, $data['controller'])
                    );
                }
            }
        }

        return $allowedAreas;
    }

    /**
     *
     * @return \Zend\Session\Container
     */
    public function getSession() {

        return new \Zend\Session\Container('default');
    }

    /**
     * Prepare common JS translations
     *
     * @return array
     */
    protected function _getControllerTranslations() {

        return array(
            'BTN_CANCEL',
            'BTN_CLOSE',
            'BTN_COLUMNS_VIEW',
            'BTN_CREATE_VIEW',
            'BTN_DELETE',
            'BTN_FILTERS',
            'BTN_GALLERY_EDIT',
            'BTN_OK',
            'BTN_SAVE_VIEW',
            'BTN_SELECT_FILE',
            'BTN_UPDATE',

            'LBL_LOCATION_NO_IMAGES',
            'LBL_PIXEL',
            'LBL_TOTAL_IMAGES',
            'LBL_UPLOAD_PREFIX',
            'LNK_REMOVE_ITEM',

            'MSG_AJAX_ERROR',
            'MSG_ALLOWED_IMAGE_FORMATS',
            'MSG_COLORPICKER',
            'MSG_CONFIRM_COPY',
            'MSG_CONFIRM_DELETE',
            'MSG_FIELD_UPDATING',
            'MSG_FILE_DELETED',
            'MSG_FILE_DELETING',
            'MSG_FILE_LOADING',
            'MSG_FILE_UPDATED',
            'MSG_FILE_UPDATING',
            'MSG_IMPORTING',
            'MSG_ONLY_ONE_FILE_ALLOWED',
            'MSG_OPTIMAL_IMAGE_SIZE',
            'MSG_SAVING',
            'MSG_SAVING_BASIC_DATA',
            'MSG_SAVING_INVOICE_DATA',
            'MSG_SAVING_PICKING_DATA',
            'MSG_SELECT_TEASER_INFO',
            'MSG_SENDING_INVOICE',
            'MSG_SENDING_INVOICE_REQUEST',
            'MSG_UPDATE_EVENTS_WITH_ACTION',
            'MSG_UPDATE_EVENTS_WITH_ACTION_DEACTIVATE',
            'MSG_UPDATE_EVENTS_WITH_ACTION_PICKING',
            'MSG_UPDATING',

            'PLH_FILE_UPLOAD',

            'TTL_ADD_FILES',
            'TTL_AJAX_ERROR',
            'TTL_BASIC_DATA',
            'TTL_COLORPICKER',
            'TTL_CONFIRM_FILE_DELETE',
            'TTL_DELETE',
            'TTL_DELETE_FILE',
            'TTL_DELETE_VIEW',
            'TTL_DESCRIPTION',
            'TTL_ERROR',
            'TTL_EXPERT_VIEW',
            'TTL_FIELD_UPDATING',
            'TTL_FILENAME',
            'TTL_FILEPREVIEW',
            'TTL_FILESIZE',
            'TTL_FILE_DELETE',
            'TTL_FILE_DELETED',
            'TTL_FILE_LOADING',
            'TTL_FILE_UPDATE',
            'TTL_FILE_UPDATED',
            'TTL_GET_ATTENDEE_LIST',
            'TTL_IMAGENAME',
            'TTL_IMAGESIZE',
            'TTL_IMPORT_FILES',
            'TTL_INVALID_FIELDS',
            'TTL_INVOICE_DATA',
            'TTL_LOAD_NAVIGATION',
            'TTL_LOGIN',
            'TTL_PICKING_DATA',
            'TTL_PLEASE_WAIT',
            'TTL_REPEAT_REQUEST',
            'TTL_REPORTING',
            'TTL_SAVE_COMPANY',
            'TTL_SAVE_FIELD',
            'TTL_SAVE_HOTEL',
            'TTL_SAVE_USER',
            'TTL_SAVE_VIEW',
            'TTL_SAVE_CATEGORY',
            'TTL_SELECT_FILE',
            'TTL_SENDING_INVOICE',
            'TTL_SENDING_INVOICE_REQUEST',
            'TTL_SEND_INVOICE',
            'TTL_SEND_REQUEST',
            'TTL_STARTIMAGE',
            'TTL_UPDATE_EVENTS_WITH_ACTION',
            'TTL_UPDATE_FILE',

            'VAL_CALENDAR_DAYS',
            'VAL_CALENDAR_MONTHS',
            'VAL_CALENDAR_WEEKS',
            'VAL_OFF',
            'VAL_ON',
            'VAL_PLEASE_SELECT'
        );
    }

    /**
     * Display an exception using a common template.
     *
     * This method should be used in all controllers und thus be located in
     * AbstractBackendController.
     *
     * @param \Zend\View\Model\ViewModel $view
     * @param \Exception $e
     */
    protected function _displayException(\Zend\View\Model\ViewModel $view, \Exception $e) {

        error_log($e->getMessage());
        error_log($e->getTraceAsString());

        $view->setTemplate('error/index');
        $view->setVariable('state', 'error');
        $view->setVariable('message', $e->getMessage());
        $this->flashMessenger()->addMessage($e->getMessage());
    }

    /**
     * Per Get index Action only is available
     *
     * @return redirect
     */
    protected function _redirectNonAjax() {

        return $this->redirect()->toRoute('home/default', array(
            'controller' => 'admin',
            'action'     => 'index'
        ));
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
        \Exception $e,
        \Zend\View\Model\ViewModel $view,
        $message = null
    ) {

        // log exception
        error_log($e->getMessage());
        error_log($e->getTraceAsString());

        // determine message to display (if none has been given)
        if (!$message) {
            if ($e instanceof \Doctrine\DBAL\DBALException) {

                // translate message if possible
                // DERTMS-967 database errors must not be displayed in UI
                $message = $this->translator->translate('ERR_DATABASE_ABSTRACTION_LAYER');
//                $message = $this->translator->translate($e->getMessage());

            } if ($e instanceof \Ffb\Common\Service\Exception\MaxFileSizeException) {

                // translate message if possible
                $message = sprintf($this->translator->translate($e->getMessage()), number_format($e->getCode() / 1000000));

            } else {

                // translate message if possible
                $message = $this->translator->translate($e->getMessage());
            }
        } else {

            // translate message if possible
            $message = $this->translator->translate($message);
        }

        // add message to flash messenger
        $this->flashMessenger()->addMessage($message);

        // set message to view model
        $view->setTemplate('error/index');
        $view->setVariable('state', 'error');
        $view->setVariable('message', $message);

        if ($e instanceof \Ffb\Eventus\Exception\InvalidFormException) {
            $view->setVariable('invalidFields', $e->invalidFields);
        }
    }

    /**
     * Returns the service with required variables
     *
     * @param string $serviceClass
     * @return \Ffb\Common\Service\AbstractService
     */
    protected function _getService($serviceClass) {

        $service = $this->getServiceLocator()->get($serviceClass);
        $service->setLogger($this->logger);
        $service->setUser($this->auth->getIdentity());
        $service->setUrl($this->url());
        $service->setTranslator($this->translator);
        $service->setFlashMessenger($this->flashMessenger());

        return $service;
    }

    /**
     * Master language code
     *
     * @return string
     */
    public function getMasterLang() {
        return $this->_masterLang;
    }

}