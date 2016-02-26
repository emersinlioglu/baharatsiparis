<?php

namespace Ffb\Backend\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use \Ffb\Backend\View\Helper;
use \Ffb\Backend\Model;
use \Ffb\Backend\Form;
use \Ffb\Backend\Entity;

use Zend\Json\Json;
use Zend\Ldap\Product;
use Zend\View\Model as ZendModel;

/**
 *
 * @author erdal.mersinlioglu
 */
class ProductGroupController extends AbstractBackendController {

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
            $productGroupId = (int)$this->params()->fromRoute('value');

            // get model(s)
            $productGroupModel = new Model\ProductGroupModel($this->getServiceLocator(), $this->logger);

            // get entities
            /* @var $productGroup Entity\ProductGroupEntity */
            $productGroup = $productGroupModel->findById($productGroupId);

            $urlParams = array(
                'controller' => 'productgroup',
                'action'     => 'form'
            );

            // prepare form
            $form  = new Form\ProductGroupForm('form-product-group', array(), $productGroupModel->getEntityManager());
            if ($productGroup) {
                $urlParams['param'] = 'id';
                $urlParams['value'] = $productGroup->getId();
            } else {
                // create new product group
                $productGroup = new Entity\ProductGroupEntity();
            }

            $form->setAttribute('action', $this->url()->fromRoute('home/default', $urlParams));
            $productGroupModel->createMissingTranslations(
                $productGroup,
                'Ffb\Backend\Entity\ProductGroupLangEntity'
            );
            $form->bind($productGroup);

            // parent productGroup
            $pgOptions = $productGroupModel->getParentsValueOptions();
            $form->get('parent')->setValueOptions($pgOptions);

            // check post
            if ($this->getRequest()->isPost()) {

                // get data
                $data = $this->getRequest()->getPost();

                // assign to form, to valiadate
                $form->setData($data);

                if ($form->isValid()) {

                    $productGroup = $form->getData();

                    if ($productGroup->getId()) {
                        // update
                        $productGroupModel->update($productGroup);
                    } else {
                        // insert
                        $productGroupModel->insert($productGroup);
                    }

                    $this->flashMessenger()->addMessage($this->translator->translate('MSG_FORM_UPDATED'));

                    $view->setVariable('productGroupUrl', $this->url()->fromRoute('home/default', array(
                        'controller' => 'product',
                        'action'     => 'subnavi',
                        'param'      => 'productgroup',
                        'value'      => $productGroup->getId()
                    )));

                } else {
                    $this->flashMessenger()->addMessage($this->translator->translate('MSG_FORM_INVALID'));
                    $view->setVariable('state', 'error');
                    $view->setVariable('invalidFields', $form->getInvalidFields());
                }

            } else {

                $view->setVariables(array(
                    'form' => $form->prepare()
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

//            // check access via ACL
//            if (!$this->checkAccess($this->auth->getIdentity(), self::AREA, 'edit', $view)) {
//                return $view;
//            }

        try {

            if ($this->getRequest()->isPost()) {

                $productGroupModel          = new Model\ProductGroupModel($this->getServiceLocator(), $this->logger);
                $productGroupProductModel = new Model\ProductGroupProductModel($this->getServiceLocator(), $this->logger);
                $productModel               = new Model\ProductModel($this->getServiceLocator(), $this->logger);

                // get data
                $data = $this->getRequest()->getPost();

                $assign = $data->get('assignProduct') === 'true' ? true : false;

                $productId      = (int)$this->params()->fromRoute('value');
                $productGroupId = (int)$this->params()->fromRoute('value2');

                /* @var $productGroup Entity\ProductGroupEntity */
                $productGroup = $productGroupModel->findById($productGroupId);
                /* @var $product Entity\ProductEntity */
                $product = $productModel->findById($productId);

                if ($assign) {
                    // add Product to ProductGroup

                    /* @var $productGroupProductEntity Entity\ProductGroupProductEntity */
                    $productGroupProductEntity = new Entity\ProductGroupProductEntity();
                    $productGroupProductEntity->setProduct($product);
                    $productGroupProductEntity->setProductGroup($productGroup);

                    $collection = new ArrayCollection();
                    $collection->add($productGroupProductEntity);

                    $product->addProductGroupProducts($collection);

                    $productModel->update($product);
                } else {

                    $productGroupProductEntity = $productGroupProductModel->findOneBy(array(
                        'product'      => $productId,
                        'productGroup' => $productGroupId
                    ));

                    //remove product from ProductGroup
                    $collection = new ArrayCollection();
                    $collection->add($productGroupProductEntity);

                    $productGroupProductModel->deleteEntity($productGroupProductEntity);
                }
            }
        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
    }

//    /**
//     * delete productgroup
//     *
//     * @return ZendModel\ViewModel
//     */
//    public function deleteAction() {
//
//        if (!$this->_isAjax) {
//            return $this->_redirectNonAjax();
//        }
//
//        $view = new ZendModel\ViewModel(array(
//            'state' => 'ok'
//        ));
//
//        try {
//
//            // check access via ACL
//            if (!$this->checkAccess($this->auth->getIdentity(), self::AREA, 'delete', $view)) {
//                return $view;
//            }
//
//            // get param(s)
//            $productGroupId = (int)$this->params()->fromRoute('value');
//
//            // get model(s)
//            $productGroupModel = new Model\ProductGroupModel($this->getServiceLocator(), $this->logger);
//
//            // get entities
//            /* @var $productGroup Entity\ProductGroupEntity */
//            $productGroup = $productGroupModel->findById($productGroupId);
//
//            // check post
//            if ($this->getRequest()->isPost()) {
//
//                // delete user
//                $productGroupModel->delete($productGroup);
//
//                $productGroups = $productGroupModel->findAll();
//                if (count($productGroups) > 0) {
//                    $productGroup = $productGroups[0];
//                    $view->setVariable('callBackUrl', $this->url()->fromRoute('home/default', array(
//                        'controller' => 'product',
//                        'action' => 'subnavi',
//                        'param' => 'productgroup',
//                        'value' => $productGroup->getId()
//                    )));
//                }
//
//                $this->flashMessenger()->addMessage($this->translator->translate('MSG_ATTRIBUTE_GROUP_DELETED'));
//            }
//
//        } catch (\Exception $ex) {
//
//            //$this->_displayException($view, $ex);
//
//            $view->setVariable('state', 'error');
//            $this->flashMessenger()->addMessage($this->translator->translate('MSG_ATTRIBUTE_GROUP_CAN_NOT_BE_DELETED'));
//        }
//
//        return $view;
//    }
//
//    /**
//     * copy productgroup
//     *
//     * @return ZendModel\ViewModel
//     */
//    public function copyAction() {
//
//        if (!$this->_isAjax) {
//            return $this->_redirectNonAjax();
//        }
//
//        $view = new ZendModel\ViewModel(array(
//            'state' => 'ok'
//        ));
//
//        try {
//
//            // check access via ACL
//            if (!$this->checkAccess($this->auth->getIdentity(), self::AREA, 'edit', $view)) {
//                return $view;
//            }
//
//            // get param(s)
//            $productGroupId = (int)$this->params()->fromRoute('value');
//
//            // get model(s)
//            $productGroupModel = new Model\ProductGroupModel($this->getServiceLocator(), $this->logger);
//
//            // get entities
//            /* @var $productGroup Entity\UserEntity */
//            $productGroup = $productGroupModel->findById($productGroupId);
//
//            // check post
//            if ($this->getRequest()->isPost()) {
//
//                // clone
//                $productGroupClone = clone $productGroup;
//
//                foreach ($productGroupClone->getTranslations() as $trans) {
//                    $trans->setName($this->translator->translate('LBL_COPY_PREFIX') . $trans->getName());
//                }
//
//                // persist
//                $productGroupModel->insert($productGroupClone);
//
//                $view->setVariable('callBackUrl', $this->url()->fromRoute('home/default', array(
//                    'controller' => 'product',
//                    'action' => 'subnavi',
//                    'param' => 'productgroup',
//                    'value' => $productGroupClone->getId()
//                )));
//
//                $this->flashMessenger()->addMessage($this->translator->translate('MSG_ATTRIBUTE_GROUP_COPIED'));
//            }
//
//        } catch (\Exception $ex) {
//
//            //$this->_displayException($view, $ex);
//
//            $view->setVariable('state', 'error');
//            $this->flashMessenger()->addMessage($this->translator->translate('MSG_ATTRIBUTE_GROUP_CAN_NOT_BE_COPIED'));
//        }
//
//        return $view;
//    }

}