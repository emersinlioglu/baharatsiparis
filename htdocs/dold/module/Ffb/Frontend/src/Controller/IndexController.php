<?php

namespace Ffb\Frontend\Controller;

use \Ffb\Frontend\View\Helper;
use Ffb\Backend\Model;
use Ffb\Backend\Entity;
use Ffb\Frontend\Form;
use Ffb\Frontend\Service;
use Zend\Json\Json;
use Zend\View\Model as ZendModel;

/**
 *
 * @author erdal.mersinlioglu
 */
class IndexController extends AbstractFrontendController {

    /**
     * Prepare controller
     */
    public function preDispatch() {
        parent::preDispatch();

        // provide layout w/ translations for JS
        $translations = array();
        foreach ($this->_getControllerTranslations() as $key) {
            $translations[$key] = $this->translator->translate($key);
        }
        $this->layout()->setVariable('JSTranslations', Json::encode($translations));
    }

    /**
     * Translations
     * @return array
     */
    protected function _getControllerTranslations() {
        return array_merge(parent::_getControllerTranslations(), array(
            'TTL_SAVE_PRODUCT'
        ));
    }

    /**
     * Index Action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction() {

        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        try {

//            // check acl access
//            if (!$this->checkAccess($this->auth->getIdentity(), self::AREA, null, $view)) {
//                return $view;
//            }

            // get models(s)
            $productLangModel  = new Model\ProductLangModel($this->getServiceLocator(), $this->logger);
            $categoryModel = new Model\CategoryModel($this->getServiceLocator(), $this->logger);

            $data = array(
                'lang' => 'de'
            );
            $productLangs = $productLangModel->getProducts($data);
            $groupedProductLangs = $productLangModel->groupProducts($productLangs);

            $categories = $categoryModel->findAll();

            // set variables
            $view->setVariables(array(
                'groupedProductLangs' => $groupedProductLangs
            ));

            if ($this->getRequest()->isPost()) {

            }

        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
    }

}