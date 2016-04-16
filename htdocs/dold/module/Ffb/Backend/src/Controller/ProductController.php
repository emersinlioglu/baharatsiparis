<?php

namespace Ffb\Backend\Controller;

use DoctrineORMModule\Proxy\__CG__\Ffb\Backend\Entity\ProductEntity;
use \Ffb\Backend\View\Helper;
use Ffb\Backend\Model;
use Ffb\Backend\Entity;
use Ffb\Backend\Form;
use Ffb\Backend\Service;
use Zend\Json\Json;
use Zend\View\Model as ZendModel;

/**
 *
 * @author erdal.mersinlioglu
 */
class ProductController extends AbstractBackendController {

    /**
     * Area name for the rights
     * @var string
     */
    const AREA = 'products';

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

            // check acl access
            if (!$this->checkAccess($this->auth->getIdentity(), self::AREA, null, $view)) {
                return $view;
            }

            // get models(s)
            $productModel  = new Model\ProductModel($this->getServiceLocator(), $this->logger);

            $linkHelper = new Helper\HtmlLinkHelper();
            $uriAddEntity = $linkHelper->getHtml(
                '',
                $this->url()->fromRoute('home/default', array(
                    'controller' => 'category',
                    'action' => 'form',
                )),
                '',
                'button gray add category'
            );

            $items = $productModel->getCategoryTree();

            // set variables
            $view->setVariables(array(
                'uriGetList'        => $this->url()->fromRoute('home/default', array(
                    'controller' => 'product',
                    'action'     => 'index'
                )),
                'urlCategorySort'        => $this->url()->fromRoute('home/default', array(
                    'controller' => 'category',
                    'action'     => 'sort'
                )),
                'uriAddEntity'      => $uriAddEntity,
                'withSubnavi'       => true,
                'paneFirstTitle'    => '&nbsp;',
                'paneSecondTitle'   => '&nbsp;',
                'paneSecondContent' => $this->_getSubNavigationHtml(),
                'items'             => $items
            ));

            if ($this->getRequest()->isPost()) {

                // POST Request
                $smartyRenderer = $this->getServiceLocator()->get('SmartyRenderer');
                $viewModel      = new ZendModel\ViewModel();
                $viewModel->setTemplate('ffb\backend/controller/product/index');
                $viewModel->setVariables($view->getVariables());

                $view->setVariables(array(
                    'entityList' => $smartyRenderer->render($viewModel)
                ));

            }

        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
    }

    /**
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function subnaviAction() {

        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        $categoryId = $this->params()->fromRoute('value', null);

        // post data
        $data = $this->getRequest()->getPost()->toArray();

        $view->setVariables(array(
            'products'        => $this->_getProductList($categoryId, $data),
            'showProductLink' => $categoryId > 0 ? true : false,
            'uriAddEntity'      => $this->url()->fromRoute('home/default', array(
                'controller' => 'product',
                'action'     => 'form',
                'param'      => 'category',
                'value'      => $categoryId
            )),
            'formSearchProduct' => $this->_getSearchForm($categoryId, $data)
        ));

        return $view;
    }

    /**
     * Returns search form
     * @param array $data Postdata
     * @return \Ffb\Backend\Form\SearchForm
     */
    private function _getSearchForm($categoryId = null, array $data = null) {

        $form = new Form\SearchForm('search-product-subnavi');
        $form->setAttribute('class', 'form-default form-search-product-subnavi form-search-subnavi');
        $form->setAttribute('action', $this->url()->fromRoute('home/default', array(
            'controller' => 'product',
            'action'     => 'subnavi',
            'param'      => 'category',
            'value'      => $categoryId
        )));

        if (!is_null($data)) {
            $form->setData($data);
        }

        $actions = array(
            array(
                'label' => 'Sortieren',
                'value' => 'no-value',
                'attributes' => array(
                    'data-sort-url' => $this->url()->fromRoute('home/default', array(
                        'controller' => 'product',
                        'action'     => 'sort',
                        'param'      => 'category',
                        'value'      => $categoryId
                    ))
                )
            )
        );
        $form->get('isSystem')->setValueOptions($actions);

        return $form->prepare();
    }

    /**
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function formAction() {
        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        try {
            // check access via ACL
            if (!$this->checkAccess($this->auth->getIdentity(), self::AREA, 'edit', $view)) {
                return $view;
            }

            // get param(s)
            $urlParams      = $this->_getUrlParamsForFormAction();

            // check post
            $postData = null;
            if ($this->getRequest()->isPost()) {
                $postData = $this->getRequest()->getPost()->toArray();
            }

            /* @var $productService Service\ProductService */
            $productService = $this->_getService('Ffb\Backend\Service\ProductService');

            $view = $productService->form($urlParams, $postData);

        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
    }

    /**
     * Returns produkt list
     *
     * @param int $categoryId
     * @param array $data Post data
     * @return array
     */
    private function _getProductList($categoryId, array $data = null) {

        // get model(s)
        $productModel = new Model\ProductModel($this->getServiceLocator(), $this->logger);
        $langModel    = new Model\LangModel($this->getServiceLocator(), $this->logger);

        $products = $productModel->findForSubnavi($data);

        $productList = array();
        $linkHelper  = new Helper\HtmlLinkHelper();
        $spanHelper  = new Helper\HtmlSpanHelper();

        /*  @var $product Entity\ProductEntity */
        foreach ($products as $product) {

            $name = $product->getCurrentTranslation()->getName();

            $isAssigned = false;
            /*@var $productCategory Entity\ProductCategoryEntity */
            foreach ($product->getProductCategories() as $productCategory) {
                $isAssigned = $categoryId == $productCategory->getCategory()->getId();
                if ($isAssigned) {
                    break;
                }
            }

            $masterTrans = $product->getCurrentTranslation($this->getMasterLang())->getName();
            $translations = array();
            foreach($langModel->getActiveLanguagesAsArray() as $langId => $langCode) {
                $translations[$langCode] = $product->getCurrentTranslation($langId)->getName();
            }

            if ($isAssigned || $categoryId == null) {
                $productList[] = array(
                    'link' => array(
                        'masterTrans' => $masterTrans,
                        'translations' => $translations,
                        'isSystem' => $product->getIsSystem(),
                        //'class' => 'pane-navi-link product' . ($product->getIsSystem() ? ' system' : ''),
                        'url' => $this->url()->fromRoute('home/default', array(
                            'controller' => 'product',
                            'action'     => 'productvariants',
                            'param'      => 'product',
                            'value'      => $product->getId(),
                            'param2'     => 'category',
                            'value2'     => $categoryId
                        )),
                        'paneTitle' => '', //'data-pane-title'
                        'deleteUrl' => $this->url()->fromRoute('home/default', array( //'data-delete-url'
                            'controller' => 'product',
                            'action'     => 'delete',
                            'param'      => 'product',
                            'value'      => $product->getId()
                        ))
                    ),
                    'span' => array(
                        'attributes' => array(
                            'data-form-url' => $this->url()->fromRoute('home/default', array( //'data-form-url'
                                'controller' => 'product',
                                'action'     => 'form',
                                'param'      => 'product',
                                'value'      => $product->getId()
                            ))
                        )
                    )
                );
            }
        }

        return $productList;
    }

    /**
     * @param null $categoryId
     *
     * @return mixed
     */
    protected function _getSubNavigationHtml($categoryId = null) {

        $productList = $this->_getProductList($categoryId);

        // render cart details
        $smartyRenderer = $this->getServiceLocator()->get('SmartyRenderer');
        $viewModel      = new ZendModel\ViewModel();
        $viewModel->setTemplate('ffb\backend/controller/product/subnavi');
        $viewModel->setVariables(array(
            'products'        => $productList,
            'showAttributeLink' => true,
            'uriAddEntity'      => $this->url()->fromRoute('home/default', array(
                'controller' => 'product',
                'action'     => 'form'
            )),
            'withSubnavi'       => true,
            'formSearchProduct' => $this->_getSearchForm()
        ));

        return $smartyRenderer->render($viewModel);
    }

    /**
     * get attribute id and attributegroup id from Url
     *
     * @return array
     */
    private function _getUrlParamsForFormAction() {

        $param      = $this->params()->fromRoute('param');
        $param2     = $this->params()->fromRoute('param2');
        $value      = $this->params()->fromRoute('value', 0);
        $value2     = $this->params()->fromRoute('value2', 0);

        $params = array(
            'productId'  => 0,
            'categoryId' => 0,
            'parentId'   => 0
        );

//        $productId = $categoryId = null;
//        if ($param == "product") {
//            $productId = $value;
//            $categoryId = $value2;
//        } else if ($param == "category") {
//            $productId = $value2;
//            $categoryId = $value;
//        }

        switch ($param) {
            case 'product':
                $params['productId']  = $value;
                $params['categoryId'] = $value2;
                break;
            case 'category':
                $params['productId']  = $value2;
                $params['categoryId'] = $value;
                break;
            case 'parent':
                $params['parentId']   = $value;
                break;
            default:
                break;
        }

        return $params;
    }

    /**
     * delete product
     *
     * @return ZendModel\ViewModel
     */
    public function deleteAction() {

        if (!$this->_isAjax) {
            return $this->_redirectNonAjax();
        }

        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        try {

            // check access via ACL
            if (!$this->checkAccess($this->auth->getIdentity(), self::AREA, 'delete', $view)) {
                return $view;
            }

            // get param(s)
            $productId = (int)$this->params()->fromRoute('value');

            // get model(s)
            $productModel = new Model\ProductModel($this->getServiceLocator(), $this->logger);

            // get entities
            /* @var $product Entity\ProductEntity */
            $product = $productModel->findById($productId);

            // check post
            if ($this->getRequest()->isPost()) {

                // category id for navigation
                $categoryId = $product->getProductCategories()->first()->getCategory()->getId();

                // delete
                $productModel->delete($product);

                // to refresh the navigation
                $view->setVariable('callBackUrl', $this->url()->fromRoute('home/default', array(
                    'controller' => 'product',
                    'action' => 'subnavi',
                    'param' => 'category',
                    'value' => $categoryId
                )));

                $this->flashMessenger()->addMessage($this->translator->translate('MSG_PRODUCT_DELETED'));
            }

        } catch (\Exception $e) {

            //$this->_displayException($view, $e);

            $view->setVariable('state', 'error');
            $this->flashMessenger()->addMessage($this->translator->translate('MSG_PRODUCT_CAN_NOT_BE_DELETED'));
        }

        return $view;
    }

    /**
     * Product variants action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function productvariantsAction() {
        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        try {

            // check access via ACL
            if (!$this->checkAccess($this->auth->getIdentity(), self::AREA, null, $view)) {
                return $view;
            }

            // get param(s)
            $productId = (int)$this->params()->fromRoute('value');

            // get model(s)
            $productModel = new Model\ProductModel($this->getServiceLocator(), $this->logger);

            // get entities
            /* @var $product Entity\ProductEntity */
            $product = $productModel->findById($productId);

            // check post
            if ($this->getRequest()->isPost()) {
                // POST Request
            } else {
                // GET Request
                $view->setVariables(array(
                    'productvariants' => $this->_getProductvariantsList($product),
                    'uriAddEntity' => $this->url()->fromRoute('home/default', array(
                        'controller' => 'product',
                        'action' => 'form',
                        'param' => 'parent',
                        'value' => $productId
                    ))
                ));
            }

        } catch (\Exception $e) {

            //$this->_displayException($view, $e);

            $view->setVariable('state', 'error');
            $this->flashMessenger()->addMessage($this->translator->translate('MSG_ERROR_OCCURED'));
        }

        return $view;
    }

    /**
     * Get product variants list
     * @param Entity\ProductEntity $product
     * @return array
     */
    private function _getProductvariantsList($product) {

        $productvariantsList = array();
        $linkHelper          = new Helper\HtmlLinkHelper();
        $spanHelper          = new Helper\HtmlSpanHelper();

        /*  @var $productvariant Entity\ProductEntity */
        foreach ($product->getChilderen() as $productvariant) {

            $categoryId = $productvariant->getProductCategories()->first()->getCategory()->getId();

            $name = $productvariant->getCurrentTranslation()->getName();

            // render link
            $liString = $linkHelper->getHtml(
                $name,
                $this->url()->fromRoute('home/default', array(
                    'controller' => 'product',
                    'action'     => 'form',
                    'param'      => 'product',
                    'value'      => $productvariant->getId(),
                    'param2'     => 'category',
                    'value2'     => $categoryId
                )),
                $name,
                'pane-navi-link productvariant',
                array(
                    'data-pane-title' => '',
                    'data-delete-url' => $this->url()->fromRoute('home/default', array(
                        'controller' => 'product',
                        'action'     => 'delete',
                        'param'      => 'product',
                        'value'      => $productvariant->getId()
                    ))
                ),
                null
            );

//            $liString .= $spanHelper->getHtml(
//                '',
//                'edit',
//                array(
//                    'data-form-url' => $this->url()->fromRoute('home/default', array(
//                        'controller' => 'product',
//                        'action'     => 'form',
//                        'param'      => 'product',
//                        'value'      => $productvariant->getId()
//                    ))
//                )
//            );

            $productvariantsList[] = $liString;
        }

        return $productvariantsList;
    }

    /**
     * Log action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function logAction() {
        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        try {

            // check access via ACL
            if (!$this->checkAccess($this->auth->getIdentity(), self::AREA, null, $view)) {
                return $view;
            }

            // get param(s)
            $productId = (int)$this->params()->fromRoute('value');

            // get model(s)
            $productModel = new Model\ProductModel($this->getServiceLocator(), $this->logger);

            // get entities
            /* @var $product Entity\ProductEntity */
            $product = $productModel->findById($productId);

            // check post
            if ($this->getRequest()->isPost()) {

                $data = $this->getRequest()->getPost()->toArray();

                /* @var $productService Service\ProductService */
                $productService = $this->_getService('Ffb\Backend\Service\ProductService');

                $attributeValueLogData = $productService->getAttributeValueLogData($product, $data);

                // POST Request
                $view->setVariables(array(
                    'attributeValueLog' => $attributeValueLogData,
                ));
            } else {
                // GET Request
            }

        } catch (\Exception $e) {

            //$this->_displayException($view, $e);

            $view->setVariable('state', 'error');
            $this->flashMessenger()->addMessage($this->translator->translate('MSG_ERROR_OCCURED'));
        }

        return $view;
    }

    /**
     * Search products action
     * @return \Zend\View\Model\ViewModel
     */
    public function searchProductsAction() {

        $view = new ZendModel\JsonModel();

        $productId  = $this->params()->fromRoute('value', null);
        $searchterm = $this->getRequest()->getQuery('term', '');
        $productRelation = $this->getRequest()->getQuery('param2');

        // get model(s)
        $productModel = new Model\ProductModel($this->getServiceLocator(), $this->logger);

        $product = $productModel->findById($productId);
        if (!$product) {
            return $view;
        }

        $products = $productModel->findForLinkedProducts(array(
            'searchterm' => $searchterm,
            'productId' => $productId
        ));

        $results = array();
        foreach ($products as $product) {
            $productName = $product->getCurrentTranslation()->getName();

            $results[] = array(
                'id' => $product->getId(),
                'label' => $productName,
                'value' => $productName,
                'delete-url' => $this->url()->fromRoute('home/default', array(
                    'controller' => 'product',
                    'action' => 'removeAssignedProduct',
                    'param' => 'product',
                    'value' => $productId,
                    'param2' => $productRelation,
                    'value2' => $product->getId()
                ))
            );
        }

        if (count($results) == 0) {
            $results[] = array(
                "id" => '',
                "label" => sprintf($this->translator->translate('MSG_NO_PRODUCT_FOUND_TO_LINK'), $searchterm),
                "value" => ''
            );
        }

        $view->setVariables($results);

        return $view;
    }

    /**
     * Add assigned product Action
     * @return \Zend\View\Model\ViewModel
     */
    public function addAssignedProductAction() {

        $view = new ZendModel\JsonModel(array(
            'state' => 'ok'
        ));

        $productId       = $this->params()->fromRoute('value', null);
        $linkedProductId = $this->params()->fromRoute('value2', null);
        $productRelation = $this->params()->fromRoute('param2');

        // get model(s)
        $productModel = new Model\ProductModel($this->getServiceLocator(), $this->logger);

        try {
            /* @var $product \Ffb\Backend\Entity\ProductEntity */
            /* @var $assignedProduct \Ffb\Backend\Entity\ProductEntity */
            $product         = $productModel->findById($productId);
            $assignedProduct = $productModel->findById($linkedProductId);

            // productService
            /* @var $productService Service\ProductService */
            $productService  = $this->_getService('Ffb\Backend\Service\ProductService');

            switch ($productRelation) {
                case Entity\ProductEntity::TYPE_LINKED_PRODUCT:
                    $hasLinkedProduct = $product->getLinkedProducts()->contains($assignedProduct);
                    if (!$hasLinkedProduct) {
                        // add linkedProduct
                        $product->getLinkedProducts()->add($assignedProduct);
                    }
                    break;

                case Entity\ProductEntity::TYPE_ACCESSORY_PRODUCT:
                    $hasAccessoryProduct = $product->getAccessoryProducts()->contains($assignedProduct);
                    if (!$hasAccessoryProduct) {
                        // add accessoryProduct
                        $product->getAccessoryProducts()->add($assignedProduct);
                    }
                    break;

                default:
                    break;
            }

            // update
            $productModel->update($product);

            $view->setVariable('assignedProductsList', $productService->getAssignedProductsList($product, $productRelation));

        } catch (Exception $e) {
            $this->_displayException($view, $e);
        }

        return $view;
    }

    /**
     * Remove AssignedProduct Action
     * @return \Zend\View\Model\ViewModel
     */
    public function removeAssignedProductAction() {

        $view = new ZendModel\JsonModel(array(
            'state' => 'ok'
        ));

        $productId       = $this->params()->fromRoute('value', null);
        $linkedProductId = $this->params()->fromRoute('value2', null);
        $productRelation = $this->params()->fromRoute('param2');

        // get model(s)
        $productModel = new Model\ProductModel($this->getServiceLocator(), $this->logger);

        // productService
        /* @var $productService Service\ProductService */
        $productService = $this->_getService('Ffb\Backend\Service\ProductService');

        try {
            /* @var $product \Ffb\Backend\Entity\ProductEntity */
            /* @var $assignedProduct \Ffb\Backend\Entity\ProductEntity */
            $product         = $productModel->findById($productId);
            $assignedProduct = $productModel->findById($linkedProductId);

            switch ($productRelation) {
                case Entity\ProductEntity::TYPE_LINKED_PRODUCT:
                    $hasLinkedProduct = $product->getLinkedProducts()->contains($assignedProduct);
                    if ($hasLinkedProduct) {
                        // remove
                        $product->getLinkedProducts()->removeElement($assignedProduct);
                    }
                    break;

                case Entity\ProductEntity::TYPE_ACCESSORY_PRODUCT:
                    $hasAccessoryProduct = $product->getAccessoryProducts()->contains($assignedProduct);
                    if ($hasAccessoryProduct) {
                        // remove
                        $product->getAccessoryProducts()->removeElement($assignedProduct);
                    }
                    break;

                default:
                    break;
            }

            // update
            $productModel->update($product);

            $view->setVariable('assignedProductsList', $productService->getAssignedProductsList($product, $productRelation));

        } catch (Exception $e) {
            $this->_displayException($view, $e);
        }

        return $view;
    }

    /**
     * MultipleUsageAssignment Action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function multipleUsageAssignmentAction() {

        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        // check access via ACL
        if (!$this->checkAccess($this->auth->getIdentity(), self::AREA, 'edit', $view)) {
            return $view;
        }

        try {

            if ($this->getRequest()->isPost()) {

                // model(s)
                $productModel   = new Model\ProductModel($this->getServiceLocator(), $this->logger);
                $categoryModel  = new Model\CategoryModel($this->getServiceLocator(), $this->logger);

                // post data
                $data = $this->getRequest()->getPost();

                // get params
                $productId     = (int)$this->params()->fromRoute('value');
                $categoryId    = (int)$this->params()->fromRoute('value2');

                // entities
                /* @var $product Entity\ProductEntity */
                $product = $productModel->findById($productId);
                /* @var $category Entity\CategoryEntity */
                $category = $categoryModel->findById($categoryId);

                // check if category is already assigned
                $isAssigned = $product->getMultipleUsages()->contains($category);

                if ($isAssigned) {
                    // remove
                    $product->getMultipleUsages()->removeElement($category);
                } else {
                    // add
                    $product->getMultipleUsages()->add($category);
                }

                // update
                $productModel->update($product);
            }
        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
    }
}