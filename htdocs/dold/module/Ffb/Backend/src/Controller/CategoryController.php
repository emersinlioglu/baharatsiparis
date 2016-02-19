<?php

namespace Ffb\Backend\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use \Ffb\Backend\Model;
use \Ffb\Backend\Form;
use \Ffb\Backend\Entity;
use \Ffb\Backend\Service;

use Zend\Json\Json;
use Zend\View\Model as ZendModel;

/**
 *
 * @author erdal.mersinlioglu
 */
class CategoryController extends AbstractBackendController {

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
        $translations = parent::_getControllerTranslations();

        return array_merge($translations, array(
            ''
        ));
    }

    /**
     * formAction
     *
     * @return ZendModel\ViewModel
     * @todo add documentation
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
            $categoryId = (int)$this->params()->fromRoute('value');

            // get model(s)
            $categoryModel = new Model\CategoryModel($this->getServiceLocator(), $this->logger);
            $productModel  = new Model\ProductModel($this->getServiceLocator(), $this->logger);

            // get entities
            /* @var $category Entity\CategoryEntity */
            $category = $categoryModel->findById($categoryId);

            $urlParams = array(
                'controller' => 'category',
                'action'     => 'form'
            );

            // prepare form
            $form  = new Form\CategoryForm('form-category', array(), $categoryModel->getEntityManager());
            if ($category) {
                $urlParams['param'] = 'category';
                $urlParams['value'] = $category->getId();
            } else {
                // create new product group
                $category = new Entity\CategoryEntity();
            }

            $form->setAttribute('action', $this->url()->fromRoute('home/default', $urlParams));
            $categoryModel->createMissingTranslations(
                $category,
                'Ffb\Backend\Entity\CategoryLangEntity'
            );
            $form->bind($category);

            // parents
            $parentsOptions = $categoryModel->getParentsValueOptions();
            $form->get('parent')->setValueOptions($parentsOptions);

            // check post
            if ($this->getRequest()->isPost()) {

                // get data
                $data = $this->getRequest()->getPost();

                // assign to form, to valiadate
                $form->setData($data);

                if ($form->isValid()) {

                    $category = $form->getData();

                    if ($category->getId()) {
                        // update
                        $categoryModel->update($category);
                    } else {
                        // insert
                        $categoryModel->insert($category);
                    }

                    $this->flashMessenger()->addMessage($this->translator->translate('MSG_FORM_UPDATED'));

                    $view->setVariable('categoryUrl', $this->url()->fromRoute('home/default', array(
                        'controller' => 'product',
                        'action'     => 'subnavi',
                        'param'      => 'category',
                        'value'      => $category->getId()
                    )));

                    $view->setVariable('categoryEditUrl', $this->url()->fromRoute('home/default', array(
                        'controller' => 'category',
                        'action'     => 'form',
                        'param'      => 'category',
                        'value'      => $category->getId()
                    )));

                } else {
                    $this->flashMessenger()->addMessage($this->translator->translate('MSG_FORM_INVALID'));
                    $view->setVariable('state', 'error');
                    $view->setVariable('invalidFields', $form->getInvalidFields());
                }

            } else {

                if ($category && $category->getId()) {

                    $productId = $category->getDefaultProduct() ? $category->getDefaultProduct()->getId() : '';
                    if (!$productId) {
                        $product = $productModel->buildDefaultForCategory($category);
                        $productId = $product->getId();
                    }

                    $categoryId = $category->getId();

                    /* @var $productService Service\ProductService */
                    $productService = $this->_getService('Ffb\Backend\Service\ProductService');
                    $productViewModel = $productService->form(array(
                        'categoryId' => $categoryId,
                        'productId' => $productId,
                        'parentId' => ''
                    ), null);
                    $view->setVariables($productViewModel->getVariables());

                    // amend product form
                    $productForm = $view->getVariable('form');
                    $productForm->setAttribute('class', $productForm->getAttribute('class') . ' form-root-product');
                    $productForm->get('isRoot')->setValue(1);
                }

                $view->setVariables(array(
                    'categoryForm' => $form->prepare(),
                    'isCategoryPersisted' => $category && $category->getId()
                ));
            }
        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
    }

    public function productAssignmentAction() {

        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        // check access via ACL
        if (!$this->checkAccess($this->auth->getIdentity(), self::AREA, 'edit', $view)) {
            return $view;
        }

        try {

            if ($this->getRequest()->isPost()) {

                $productModel           = new Model\ProductModel($this->getServiceLocator(), $this->logger);
                $categoryModel          = new Model\CategoryModel($this->getServiceLocator(), $this->logger);
                $productCategoryModel   = new Model\CategoryProductModel($this->getServiceLocator(), $this->logger);

                // get data
                $data = $this->getRequest()->getPost();

                $assign = $data->get('assignProduct') === 'true' ? true : false;

                $productId      = (int)$this->params()->fromRoute('value');
                $categoryId     = (int)$this->params()->fromRoute('value2');

                /* @var $category Entity\CategoryEntity */
                $category = $categoryModel->findById($categoryId);
                /* @var $product Entity\ProductEntity */
                $product = $productModel->findById($productId);

                if ($assign) {
                    // add Product to Category

                    /* @var $productCategory Entity\ProductCategoryEntity */
                    $productCategory = new Entity\CategoryProductEntity();
                    $productCategory->setProduct($product);
                    $productCategory->setCategory($category);

                    $collection = new ArrayCollection();
                    $collection->add($productCategory);

                    $product->addCategoryProducts($collection);

                    $productModel->update($product);
                } else {

                    $productCategory = $productCategoryModel->findOneBy(array(
                        'product'      => $productId,
                        'productGroup' => $categoryId
                    ));

                    //remove product from Category
                    $collection = new ArrayCollection();
                    $collection->add($productCategory);

                    $productCategoryModel->deleteEntity($productCategory);
                }
            }
        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
    }

    /**
     * delete
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
            $categoryId = (int)$this->params()->fromRoute('value');

            // get model(s)
            $categoryModel = new Model\CategoryModel($this->getServiceLocator(), $this->logger);

            // get entities
            /* @var $category Entity\CategoryEntity */
            $category = $categoryModel->findById($categoryId);

            // check post
            if ($this->getRequest()->isPost()) {

                // delete user
                $categoryModel->delete($category);

                $categories = $categoryModel->findAll();
                if (count($categories) > 0) {
                    $category = $categories[0];
                    $view->setVariable('callBackUrl', $this->url()->fromRoute('home/default', array(
                        'controller' => 'product',
                        'action' => 'subnavi',
                        'param' => 'category',
                        'value' => $category->getId()
                    )));
                }

                $this->flashMessenger()->addMessage($this->translator->translate('MSG_CATEGORY_DELETED'));
            }

        } catch (\Exception $ex) {

            //$this->_displayException($view, $ex);

            $view->setVariable('state', 'error');
            $this->flashMessenger()->addMessage($this->translator->translate('MSG_CATEGORY_CAN_NOT_BE_DELETED'));
        }

        return $view;
    }

    /**
     * copy
     *
     * @return ZendModel\ViewModel
     */
    public function copyAction() {

        if (!$this->_isAjax) {
            return $this->_redirectNonAjax();
        }

        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        try {

            // check access via ACL
            if (!$this->checkAccess($this->auth->getIdentity(), self::AREA, 'edit', $view)) {
                return $view;
            }

            // get param(s)
            $categoryId = (int)$this->params()->fromRoute('value');

            // get model(s)
            $categoryModel = new Model\CategoryModel($this->getServiceLocator(), $this->logger);

            // get entities
            /* @var $category Entity\CategoryEntity */
            $category = $categoryModel->findById($categoryId);

            // check post
            if ($this->getRequest()->isPost()) {

                // clone
                $categoryClone = clone $category;

                foreach ($categoryClone->getTranslations() as $trans) {
                    $trans->setName($this->translator->translate('LBL_COPY_PREFIX') . $trans->getName());
                }

                // persist
                $categoryModel->insert($categoryClone);

                $view->setVariable('callBackUrl', $this->url()->fromRoute('home/default', array(
                    'controller' => 'product',
                    'action' => 'subnavi',
                    'param' => 'category',
                    'value' => $categoryClone->getId()
                )));

                $this->flashMessenger()->addMessage($this->translator->translate('MSG_CATEGORY_COPIED'));
            }

        } catch (\Exception $ex) {

            //$this->_displayException($view, $ex);

            $view->setVariable('state', 'error');
            $this->flashMessenger()->addMessage($this->translator->translate('MSG_CATEGORY_CAN_NOT_BE_COPIED'));
        }

        return $view;
    }

}