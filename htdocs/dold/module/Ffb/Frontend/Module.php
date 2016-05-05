<?php
namespace Ffb\Frontend;

use Ffb\Common\Module\CommonModule;

use Imagine\Gd\Imagine;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\Validator\AbstractValidator;

/**
 * Backend Module class.
 * Contains logic for Backend module.
 *
 * Loading order: getAutoloaderConfig, init, getConfig, getServiceConfig, getControllerConfig
 * getControllerPluginConfig, getViewHelperConfig, onBootstrap
 *
 * @package Ffb
 */
class Module extends CommonModule {

    /**
     * Init module
     *
     * @see \Ffb\Common\Module\CommonModule::init()
     */
    public function init() {
        parent::init();
    }

    /**
     * Module is loaded callback
     *
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e) {
        parent::onBootstrap($e);

        // get manager
        $eventManager        = $e->getApplication()->getEventManager();
        $serviceManager      = $e->getApplication()->getServiceManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        //Init correct datetime fro oracle date object
        // $em = $e->getApplication()->getServiceManager()->get('Doctrine\ORM\EntityManager');
        // $em->getConnection()->query('ALTER SESSION SET NLS_DATE_FORMAT="YYYY-MM-DD HH24:MI:SS"');

        // set translator for form validation error messages
        $translator = $serviceManager->get('translator');
        AbstractValidator::setDefaultTranslator($translator);
    }

    /**
     * Returns autoloader config
     * @return array
     */
    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src',
                    'Ffb\Backend' => __DIR__ . '/../Backend/src'
                ),
            ),
        );
    }

    /**
     * Returns the configuration for the current module
     * @param string $moduleDir
     * @param string $appDir
     * @param string $namespace
     * @return array
     */
    public function getConfig($moduleDir = __DIR__, $appDir = null, $namespace = null) {
        return parent::getConfig($moduleDir, $appDir, $namespace);
    }

    /**
     * In order to always use the same instance of our UserTable, we will use
     * the ServiceManager to define how to create one.
     * This is most easily done in the Module class where we create a method
     * called getServiceConfig() which is automatically called by the
     * ModuleManager and applied to the ServiceManager. We’ll then be able to
     * retrieve it in our controller when we need it.
     *
     * To configure the ServiceManager, we can either supply the name of the
     * class to be instantiated or a factory (closure or callback) that
     * instantiates the object when the ServiceManager needs it. We start by
     * implementing getServiceConfig() to provide a factory that creates an
     * UserTable.
     *
     * This method returns an array of factories that are all merged together by
     * the ModuleManager before passing to the ServiceManager. The factory for
     * User\Model\UserTable uses the ServiceManager to create an
     * UserTableGateway to pass to the UserTable. We also tell the
     * ServiceManager that an UserTableGateway is created by getting a
     * Zend\Db\Adapter\Adapter (also from the ServiceManager) and using it to
     * create a TableGateway object. The TableGateway is told to use an User
     * object whenever it creates a new result row. The TableGateway classes use
     * the prototype pattern for creation of result sets and entities. This
     * means that instead of instantiating when required, the system clones a
     * previously instantiated object. See PHP Constructor Best Practices and
     * the Prototype Pattern for more details.
     *
     * @author marcus.gnass
     * @return multitype:multitype:NULL
     *         |\User\UserTable|\Zend\Db\TableGateway\TableGateway
     * @see http://framework.zend.com/manual/2.1/en/user-guide/database-and-models.html
     */
    public function getServiceConfig() {

        // To access own instance within anonymous functions in PHP < 5.4
        $module = $this;

        // @TODO: We need to set user!
        return array(
            'factories' => array(
                'Ffb\Frontend\Service\UploadService' => function(ServiceManager $sm) {
                    return new \Ffb\Frontend\Service\UploadService($sm);
                },
                'Ffb\Frontend\Service\MailService' => function(ServiceManager $sm) {
                    return new \Ffb\Frontend\Service\MailService($sm);
                },
                'Ffb\Frontend\Service\ProductService' => function(ServiceManager $sm) {
                    return new \Ffb\Frontend\Service\ProductService($sm);
                },
                'Ffb\Common\Image\ImageManager' => function(ServiceManager $sm) use ($module) {
                    $cfg = $module->getConfig();

                    // We support GD or Imagick
                    if ('GD' === $cfg['images']['library']) {
                        $imagine = new \Imagine\Gd\Imagine();
                    } else {
                        $imagine = new \Imagine\Imagick\Imagine();
                    }

                    $imageMngr = new \Ffb\Common\Image\ImageManager();

                    $imageMngr->setImagine($imagine);
                    $imageMngr->setConfig($cfg['images']['default']);

                    return $imageMngr;
                }
            )
        );
    }

    public function getControllerConfig() {
        return array();
    }

    public function getControllerPluginConfig() {
        return array();
    }

    public function getViewHelperConfig() {
        return array(
            // 'invokables' => array(
            //     'myNewHelper' => 'Application\View\Helper\MarkIfNew',
            // ),
            // 'factories' => array(
            //     'getTotalValue' => function ($helperPluginManager) {
            //         $serviceLocator = $helperPluginManager->getServiceLocator();
            //         $viewHelper = new View\Helper\GetTotalValue();
            //         $viewHelper->setServiceLocator($serviceLocator);
            //         return $viewHelper;
            //     }
            // )
        );
    }
}
