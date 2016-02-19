<?php
namespace Ffb\Common\Mvc\View\Http;

use Zend\Mvc\View\Http\InjectTemplateListener as ZendInjectTemplateListener;

/**
 *
 * @author ilja.schwarz
 */
class InjectTemplateListener extends ZendInjectTemplateListener {

    /**
     * Determine the top-level namespace of the controller
     *
     * Overrides the Zend default method to derive the module namespace.
     * Zend seems only support a top level namespace as module name.
     * We search in the controller name until "Controller" namespace
     * and assume this to be the module namespace.
     *
     * @param  string $controller
     * @return string
     * @see \Zend\Mvc\View\Http\InjectTemplateListener::deriveModuleNamespace()
     */
    protected function deriveModuleNamespace($controller) {
        if (!strstr($controller, '\\')) {
            return '';
        }

        $moduleNamespaces = array();
        foreach (explode('\\', $controller) as $namespace) {
            if ($namespace == 'Controller') {
                break;
            }

            $moduleNamespaces[] = $namespace;
        }

        if (empty($moduleNamespaces)) {
            return '';
        }

        return implode('\\', $moduleNamespaces);
    }

}
