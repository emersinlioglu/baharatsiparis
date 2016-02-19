<?php

namespace Ffb\Common\Module;

/**
 * CommonModule class.
 * Contains logic for all modules.
 *
 * @package Ffb\Common\Module
 */
class CommonModule {

    /**
     * Initializes the module
     */
    public function init() {}

    /**
     * Executes all needed functionality on bootstrapping the module.
     * These function are executed for every module.
     *
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function onBootstrap(\Zend\Mvc\MvcEvent $e) {

        // attach injectTemplate it dispatch event
        $callback = array(
            new \Ffb\Common\Mvc\View\Http\InjectTemplateListener(),
            'injectTemplate'
        );
        /* @var $sharedEvents \Zend\EventManager\SharedEventManagerInterface */
        $sharedManager = $e->getApplication()->getEventManager()->getSharedManager();
        $sharedManager->attach('Ffb', \Zend\Mvc\MvcEvent::EVENT_DISPATCH, $callback, -81);

        // set php settings
        $appConf = $e->getApplication()->getServiceManager()->get('Application\Config');
        if ($appConf['php_settings'] && 0 < count($appConf['php_settings'])) {
            // this solution has problems with names that contain dots!
            //$this->_setPhpSettings($php_settings);
            foreach ($appConf['php_settings'] as $key => $value) {
                $this->_setPhpSetting($key, $value);
            }
        }

    }

    /**
     * Set array of php settings.
     * This solution has problems with names that contain dots!
     *
     * @param array $settings
     */
    private function _setPhpSettings(array $settings) {

        foreach ($settings as $settingName => $value) {
            ini_set($settingName, $value);
        }

    }

    /**
     * Set single php setting.
     *
     * @param $key
     * @param $value
     */
    private function _setPhpSetting($key, $value) {

        if (is_array($value)) {
            foreach ($value as $subkey => $subvalue) {
                $this->_setPhpSetting(implode('.', array($key, $subkey)), $subvalue);
            }
        } else {
            ini_set($key, $value);
        }

    }

    /**
     * Returns the configuration for the current module by merging it with the global configuration.
     * @param string $moduleDir
     * @param string $appDir
     * @param string $namespace
     * @return array
     */
    public function getConfig($moduleDir, $appDir = null, $namespace = null) {

        return \Ffb\Common\Config\Loader::getModuleConfig($moduleDir, $appDir, $namespace);

    }

}
