<?php
namespace Ffb\Common\Config;

use Ffb\Common\Stdlib\ArrayUtils;

/**
 * Configuration loader class.
 * Fetches the configuration for a given file in a given directory and inherits multiple environment files.
 *
 * @package Ffb\Common\Config
 */
class Loader {

    /**
     * Array with environment inheritance.
     * @var array
     */
    private static $_inheritanceLogic = array(
        'production',
        'staging',
        'testing',
        'unittest_testing',
        'development',
        'unittest_development'
    );

    /**
     * Get global app config and fetch with app config
     *
     * @param string $appDir
     * @param array $whiteModulesList
     * @return array
     */
    public static function getApplicationConfig($appDir, $whiteModulesList = null) {

        $appConfig = ArrayUtils::merge(
            self::fetch($appDir . '/../config/app', 'application.ini'),
            self::fetch($appDir . '/config', 'application.ini')
        );

        // load client specification
        if (defined('APPLICATION_CLIENT')) {

            if (
                array_key_exists('client_specification', $appConfig) &&
                is_array($appConfig['client_specification'])         &&
                is_array($appConfig['client_specification'][APPLICATION_CLIENT])
            ) {
                $appConfig = ArrayUtils::merge(
                    $appConfig,
                    $appConfig['client_specification'][APPLICATION_CLIENT]
                );
            }
        }

        $map = array(
            'APP_DIR' => $appDir
        );
        $result = self::rewriteConstants($appConfig, $map);

        // Check modules white list
        foreach ($result['modules'] as $key => $module) {

            if ($whiteModulesList && !in_array($module, $whiteModulesList)) {
                unset($result['modules'][$key]);
            }
        }

        return $result;
    }

    /**
     * Get global module config and fetch with module config
     *
     * @param string $moduleDir
     * @param string $appDir
     * @return array
     */
    public static function getModuleConfig($moduleDir, $appDir = null) {

        if (!$appDir) {
            $appDir = $moduleDir . '/../../..';
        }

        $moduleConfig = ArrayUtils::merge(
            self::fetch($appDir . '/../config/module', 'module.ini'),
            self::fetch($moduleDir . '/config', 'module.ini')
        );

        $isHttps = isset($_SERVER['HTTPS']) && 'ON' === strtoupper($_SERVER['HTTPS']);
        if (isset($_SERVER['HTTP_HOST'])) {
            $basePath = $isHttps ? 'https://' . $_SERVER['HTTP_HOST'] : 'http://' . $_SERVER['HTTP_HOST'];
        } else {
            $basePath = '';
        }

        // load client specification
        if (defined('APPLICATION_CLIENT')) {

            if (
                array_key_exists('client_specification', $moduleConfig) &&
                is_array($moduleConfig['client_specification'])         &&
                array_key_exists(APPLICATION_CLIENT, $moduleConfig['client_specification']) &&
                is_array($moduleConfig['client_specification'][APPLICATION_CLIENT])
            ) {
                $moduleConfig = ArrayUtils::merge(
                    $moduleConfig,
                    $moduleConfig['client_specification'][APPLICATION_CLIENT]
                );
            }
        }

        $map = array(
            'APP_DIR'    => $appDir,
            'BASE_PATH'  => $basePath,
            'MODULE_DIR' => $moduleDir,
        );
        $result = self::rewriteConstants($moduleConfig, $map);

        // check force_https
        if (   isset($result['view_manager']['force_https'])
            && (int)$result['view_manager']['force_https'] === 1
        ) {
            $result['view_manager']['base_path'] = str_replace('http://', 'https://', $result['view_manager']['base_path']);

            // TODO: find how to set schema in https, couse rewe server support port 80
        }

        return $result;
    }

    /**
     * Fetches the configuration array which is loaded through all environment folders.
     *
     * @param string $directory
     * @param string $name
     *
     * @return array
     */
    public static function fetch($directory, $name) {

        $configuration = array();
        $config = new \Zend\Config\Reader\Ini();

        // iterate over all environments
        foreach (self::$_inheritanceLogic as $envDirectory) {

            // skip this environment if configuration file does not exist
            if (file_exists($directory . '/' . $envDirectory . '/' . $name) === false) {
                continue;
            }

            // merge the configuration with previous one together
            $configuration = ArrayUtils::merge(
                $configuration,
                $config->fromFile($directory . '/' . $envDirectory . '/' . $name)
            );

            // break the inheritance logic if we arrived in out current environment
            if ($envDirectory === APPLICATION_ENV) {
                break;
            }
        }

        // rewrite all constants to their actual values
        $result = new \Zend\Config\Config($configuration, true);

        return $result->toArray();
    }

    /**
     * Rewrite constants in ini file to real values from map
     *
     * @param array $configuration
     * @param array $map
     * @return array
     */
    public static function rewriteConstants($configuration, $map) {

        $config    = new \Zend\Config\Config($configuration, true);
        $processor = new \Zend\Config\Processor\Constant();
        $processor->setTokens($map);
        $processor->process($config);

        return $config->toArray();

    }
}
