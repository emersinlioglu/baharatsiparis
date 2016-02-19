<?php

//Include zend Autoloader classes
include __DIR__ . '/../vendors/Zend/Loader/AutoloaderFactory.php';
include __DIR__ . '/../vendors/Zend/Loader/ClassMapAutoloader.php';
include __DIR__ . '/../vendors/PHPExcel/PHPExcel/Writer/IWriter.php';
include __DIR__ . '/../vendors/PHPExcel/PHPExcel/Writer/Abstract.php';
include __DIR__ . '/../vendors/PHPExcel/PHPExcel/Writer/Excel5.php';
include __DIR__ . '/../vendors/PHPExcel/PHPExcel/IOFactory.php';

//Init Autoloader
Zend\Loader\AutoloaderFactory::factory(array(
    'Zend\Loader\ClassMapAutoloader' => array(
        'Ffb'               => __DIR__ . '/../vendors/Ffb/autoload_classmap.php',
        'zf'                => __DIR__ . '/../vendors/Zend/autoload_classmap.php',
        'Smarty'            => __DIR__ . '/../vendors/Smarty/autoload_classmap.php',
        'Doctrine'          => __DIR__ . '/../vendors/Doctrine/autoload_classmap.php',
        'DoctrineModule'    => __DIR__ . '/../vendors/DoctrineModule/autoload_classmap.php',
        'DoctrineORMModule' => __DIR__ . '/../vendors/DoctrineORMModule/autoload_classmap.php',
        'Imagine'           => __DIR__ . '/../vendors/Imagine/autoload_classmap.php',
        'PHPExcel'          => __DIR__ . '/../vendors/PHPExcel/autoload_classmap.php',
    ),
    'Zend\Loader\StandardAutoloader' => array(
        'namespaces' => array(

        ),
        'prefixes' => array(

        ),
    ),
));

if (!class_exists('Zend\Loader\AutoloaderFactory')) {
    throw new RuntimeException('Unable to load ZF2. Run `php composer.phar install` or define a ZF2_PATH environment variable.');
}
