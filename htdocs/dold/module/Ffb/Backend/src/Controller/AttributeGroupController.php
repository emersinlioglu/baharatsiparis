<?php

namespace Ffb\Backend\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use \Ffb\Backend\View\Helper;
use \Ffb\Backend\Model;
use \Ffb\Backend\Form;
use \Ffb\Backend\Entity;

use Zend\Json\Json;
use Zend\Ldap\Attribute;
use Zend\View\Model as ZendModel;

/**
 *
 * @author erdal.mersinlioglu
 */
class AttributeGroupController extends AbstractBackendController {

    /**
     * Area name for the rights
     * @var string
     */
    const AREA = 'attributes';

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
     * FormAction
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
            $attributeGroupId = (int)$this->params()->fromRoute('value');

            // get model(s)
            $attributeGroupModel = new Model\AttributeGroupModel($this->getServiceLocator(), $this->logger);

            // get entities
            /* @var $attributeGroup Entity\AttributeGroupEntity */
            $attributeGroup = $attributeGroupModel->findById($attributeGroupId);

            $urlParams = array(
                'controller' => 'attributegroup',
                'action'     => 'form'
            );

            // prepare form
            $form  = new Form\AttributeGroupForm('form-attribute-group', array(), $attributeGroupModel->getEntityManager());
            if ($attributeGroup) {
                $urlParams['param'] = 'id';
                $urlParams['value'] = $attributeGroup->getId();

                $formSortEntities = $this->_getFormSortEntities($attributeGroup);

            } else {
                // create new attribute group
                $attributeGroup = new Entity\AttributeGroupEntity();
            }

            $form->setAttribute('action', $this->url()->fromRoute('home/default', $urlParams));
            $attributeGroupModel->createMissingTranslations(
                $attributeGroup,
                'Ffb\Backend\Entity\AttributeGroupLangEntity'
            );
            $form->bind($attributeGroup);

            // check post
            if ($this->getRequest()->isPost()) {

                // get data
                $data = $this->getRequest()->getPost();

                // assign to form, to valiadate
                $form->setData($data);

                if ($form->isValid()) {

                    $attributeGroup = $form->getData();

                    if ($attributeGroup->getId()) {
                        // update
                        $attributeGroupModel->update($attributeGroup);
                    } else {
                        // insert
                        $attributeGroupModel->insert($attributeGroup);
                    }

                    $this->flashMessenger()->addMessage($this->translator->translate('MSG_FORM_UPDATED'));

                    $view->setVariable('attributeGroupUrl', $this->url()->fromRoute('home/default', array(
                        'controller' => 'attribute',
                        'action'     => 'subnavi',
                        'param'      => 'attributegroup',
                        'value'      => $attributeGroup->getId()
                    )));

                } else {
                    $this->flashMessenger()->addMessage($this->translator->translate('MSG_FORM_INVALID'));
                    $view->setVariable('state', 'error');
                    $view->setVariable('invalidFields', $form->getInvalidFields());
                }

            } else {

                $view->setVariables(array(
                    'form' => $form->prepare(),
                    'formSortEntities' => $attributeGroup->getId() ? $formSortEntities->prepare() : null
                ));
            }
        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
    }

    public function attributeAssignmentAction() {

        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        // check access via ACL
        if (!$this->checkAccess($this->auth->getIdentity(), self::AREA, 'edit', $view)) {
            return $view;
        }

        try {

            if ($this->getRequest()->isPost()) {

                $attributeGroupModel          = new Model\AttributeGroupModel($this->getServiceLocator(), $this->logger);
                $attributeGroupAttributeModel = new Model\AttributeGroupAttributeModel($this->getServiceLocator(), $this->logger);
                $attributeModel               = new Model\AttributeModel($this->getServiceLocator(), $this->logger);

                // get data
                $data = $this->getRequest()->getPost();

                $assign = $data->get('assignAttribute') === 'true' ? true : false;

                $attributeId      = (int)$this->params()->fromRoute('value');
                $attributeGroupId = (int)$this->params()->fromRoute('value2');

                /* @var $attributeGroup Entity\AttributeGroupEntity */
                $attributeGroup = $attributeGroupModel->findById($attributeGroupId);
                /* @var $attribute Entity\AttributeEntity */
                $attribute = $attributeModel->findById($attributeId);

                if ($assign) {
                    // add Attribute to AttributeGroup

                    /* @var $attributeGroupAttributeEntity Entity\AttributeGroupAttributeEntity */
                    $attributeGroupAttributeEntity = new Entity\AttributeGroupAttributeEntity();
                    $attributeGroupAttributeEntity->setAttribute($attribute);
                    $attributeGroupAttributeEntity->setAttributeGroup($attributeGroup);
                    $attributeGroupAttributeEntity->setSort(0);

                    $collection = new ArrayCollection();
                    $collection->add($attributeGroupAttributeEntity);

                    $attribute->addAttributeGroupAttributes($collection);

                    $attributeModel->update($attribute);
                } else {

                    $attributeGroupAttributeEntity = $attributeGroupAttributeModel->findOneBy(array(
                        'attribute'      => $attributeId,
                        'attributeGroup' => $attributeGroupId
                    ));

                    //remove attribute from AttributeGroup
                    $collection = new ArrayCollection();
                    $collection->add($attributeGroupAttributeEntity);

                    $attributeGroupAttributeModel->deleteEntity($attributeGroupAttributeEntity);
                }
            }
        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
    }

    /**
     * delete attributegroup
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
            $attributeGroupId = (int)$this->params()->fromRoute('value');

            // get model(s)
            $attributeGroupModel = new Model\AttributeGroupModel($this->getServiceLocator(), $this->logger);

            // get entities
            /* @var $attributeGroup Entity\AttributeGroupEntity */
            $attributeGroup = $attributeGroupModel->findById($attributeGroupId);

            // check post
            if ($this->getRequest()->isPost()) {

                // delete user
                $attributeGroupModel->delete($attributeGroup);

                $attributeGroups = $attributeGroupModel->findAll();
                if (count($attributeGroups) > 0) {
                    $attributeGroup = $attributeGroups[0];
                    $view->setVariable('callBackUrl', $this->url()->fromRoute('home/default', array(
                        'controller' => 'attribute',
                        'action' => 'subnavi',
                        'param' => 'attributegroup',
                        'value' => $attributeGroup->getId()
                    )));
                }

                $this->flashMessenger()->addMessage($this->translator->translate('MSG_ATTRIBUTE_GROUP_DELETED'));
            }

        } catch (\Exception $ex) {

            //$this->_displayException($view, $ex);

            $view->setVariable('state', 'error');
            $this->flashMessenger()->addMessage($this->translator->translate('MSG_ATTRIBUTE_GROUP_CAN_NOT_BE_DELETED'));
        }

        return $view;
    }

    /**
     * Sort attribute group attributes
     * @return \Zend\View\Model\ViewModel
     */
    public function sortAttributesAction() {

        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        try {
            // check access via ACL
            if (!$this->checkAccess($this->auth->getIdentity(), self::AREA, 'edit', $view)) {
                return $view;
            }

            // check post
            if ($this->getRequest()->isPost()) {

                $attributeGroupId = $this->params()->fromRoute('value');

                $attributeGroupModel = new Model\AttributeGroupModel($this->getServiceLocator(), $this->logger);
                $attributeModel      = new Model\AttributeModel($this->getServiceLocator(), $this->logger);
                $attributeGroup      = $attributeGroupModel->findById($attributeGroupId);

                // form
                $form = $this->_getFormSortEntities($attributeGroup);
                $form->bind($attributeGroup);

                // get data
                $data = $this->getRequest()->getPost();

                // assign to form, to valiadate
                $form->setData($data);

                if ($form->isValid()) {

                    $attributeGroup = $form->getData();

                    if ($attributeGroup->getId()) {
                        // update
                        $attributeGroupModel->update($attributeGroup);
                    }

                    $this->flashMessenger()->addMessage($this->translator->translate('MSG_FORM_UPDATED'));

                } else {
                    $this->flashMessenger()->addMessage($this->translator->translate('MSG_FORM_INVALID'));
                    $view->setVariable('state', 'error');
                    $view->setVariable('invalidFields', $form->getInvalidFields());
                }
            }
        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
    }

    /**
     * copy attributegroup
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
            $attributeGroupId = (int)$this->params()->fromRoute('value');

            // get model(s)
            $attributeGroupModel = new Model\AttributeGroupModel($this->getServiceLocator(), $this->logger);

            // get entities
            /* @var $attributeGroup Entity\UserEntity */
            $attributeGroup = $attributeGroupModel->findById($attributeGroupId);

            // check post
            if ($this->getRequest()->isPost()) {

                // clone
                $attributeGroupClone = clone $attributeGroup;

                foreach ($attributeGroupClone->getTranslations() as $trans) {
                    $trans->setName($this->translator->translate('LBL_COPY_PREFIX') . $trans->getName());
                }

                // persist
                $attributeGroupModel->insert($attributeGroupClone);

                $view->setVariable('callBackUrl', $this->url()->fromRoute('home/default', array(
                    'controller' => 'attribute',
                    'action' => 'subnavi',
                    'param' => 'attributegroup',
                    'value' => $attributeGroupClone->getId()
                )));

                $this->flashMessenger()->addMessage($this->translator->translate('MSG_ATTRIBUTE_GROUP_COPIED'));
            }

        } catch (\Exception $ex) {

            //$this->_displayException($view, $ex);

            $view->setVariable('state', 'error');
            $this->flashMessenger()->addMessage($this->translator->translate('MSG_ATTRIBUTE_GROUP_CAN_NOT_BE_COPIED'));
        }

        return $view;
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
     * Returns attribute group form
     *
     * @param Entity\AttributeGroupEntity $attributeGroup
     *
     * @return Form\AttributeGroupForm
     */
    protected function _getFormSortEntities(Entity\AttributeGroupEntity $attributeGroup) {

        $attributeModel = new Model\AttributeModel($this->getServiceLocator(), $this->logger);

        $urlParams = array(
            'controller' => 'attributegroup',
            'action'     => 'sortAttributes',
            'param'      => 'attributegroup',
            'value'      => $attributeGroup->getId()
        );

        // attributeGroupForm
        $options = array(
            'fieldsetName'          => 'AttributeGroupAttributeFieldset',
            'collectionFieldName'   => 'attributeGroupAttributes'
        );
        $form = new Form\SortEntitiesForm('form-sort-entities', $options, $attributeModel->getEntityManager());
        $form->setAttribute('action', $this->url()->fromRoute('home/default', $urlParams));
        $form->setAttribute('class', $form->getAttribute('class') . ' form-sort-entities');
        $form->bind($attributeGroup);

        return $form;
    }
}