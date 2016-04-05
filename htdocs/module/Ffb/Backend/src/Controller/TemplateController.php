<?php

namespace Ffb\Backend\Controller;

use Ffb\Backend\Model;
use Ffb\Backend\Entity;
use Ffb\Backend\Form;
use Ffb\Backend\Service;
use Ffb\Backend\View\Helper;

use Zend\Json\Json;
use Zend\View\Model as ZendModel;
use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 * @author erdal.mersinlioglu
 */
class TemplateController extends AbstractBackendController {

    /**
     * Area name for the rights
     * @var string
     */
    const AREA = 'templates';

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
     * Controller translations
     * @return array
     */
    protected function _getControllerTranslations() {
        return array_merge(parent::_getControllerTranslations(), array(
            'TTL_ERROR',
            'TTL_SAVE_ATTRIBUTE',
        ));
    }

    /**
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function subnaviAction() {

        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        // param(s)
        $templateId = $this->params()->fromRoute('value', null);

        // get model(s)
        $templateModel = new Model\TemplateModel($this->getServiceLocator(), $this->logger);

        $template = $templateModel->findById($templateId);

        $view->setVariables(array(
            'attributeGroups' => $this->_getAttributeGroupsList($template),
            //'showAttributeLink' => true,
            //'uriAddEntity'      => $this->url()->fromRoute('home/default', array(
            //    'controller'  => 'attribute',
            //    'action'      => 'form',
            //    'param'       => 'attributegroup',
            //    'value'       => $attributeGroupId
            //))
        ));

        return $view;
    }

    /**
     * Returns the attribute groups list
     *
     * @param \Ffb\Backend\Entity\TemplateEntity $template
     * @return array
     */
    private function _getAttributeGroupsList(Entity\TemplateEntity $template) {

        // get model(s)
        $attributeGroupModel = new Model\AttributeGroupModel($this->getServiceLocator(), $this->logger);
        $langModel           = new Model\LangModel($this->getServiceLocator(), $this->logger);
        $attributeGroupsList = array();
        $linkHelper          = new Helper\HtmlLinkHelper();
        $checkboxHelper      = new \Zend\Form\View\Helper\FormCheckbox();

        // checkbox
        $checkbox = new \Zend\Form\Element\Checkbox('assigned');
        //$checkbox->setUseHiddenElement(true);
        $checkbox->setCheckedValue(1);
        $checkbox->setUncheckedValue(0);

        /*  @var $attributeGroup Entity\AttributeGroupEntity */
        foreach ($attributeGroupModel->findAll() as $attributeGroup) {

            //$name = $attributeGroup->getCurrentTranslation()->getName();
            $data = array();

            $isAssigned = false;
            /* @var $attributeGroupAttribute Entity\AttributeGroupAttributeEntity */
            foreach ($template->getTemplateAttributeGroups() as $templateAttributeGroup) {
                $isAssigned = $attributeGroup->getId() == $templateAttributeGroup->getAttributeGroup()->getId();
                if ($isAssigned) {
                    break;
                }
            }

            // set checkbox value for isAssigned and add attribute id for better selection in js
            $checkbox->setValue($isAssigned);
            $checkbox->setAttribute('data-href', $this->url()->fromRoute('home/default', array(
                'controller' => 'template',
                'action' => 'attributeGroupAssignment',
                'param' => 'template',
                'value' => $template->getId(),
                'param2' => 'attributegroup',
                'value2' => $attributeGroup->getId()
            )));

            // render checkbox
            $data['checkbox'] = $checkboxHelper->render($checkbox);

            $masterTrans = $attributeGroup->getCurrentTranslation($this->getMasterLang())->getName();
            $translations = array();
            foreach($langModel->getActiveLanguagesAsArray() as $langId => $langCode) {
                $translations[$langCode] = $attributeGroup->getCurrentTranslation($langId)->getName();
            }

            // render link
            $data['link'] = array(
                'masterTrans' => $masterTrans,
                'translations' => $translations,
                //'class' => 'pane-navi-link attribute-group',
                'url' => $this->url()->fromRoute('home/default', array(
                    'controller' => 'template',
                    'action'     => 'attributes',
                    'param'      => 'template',
                    'value'      => $template->getId(),
                    'param2'     => 'attributegroup',
                    'value2'     => $attributeGroup->getId()
                )),
                'paneTitle' => ''
            );
//            $data['link'] = $linkHelper->getHtml(
//                $name,
//                $this->url()->fromRoute('home/default', array(
//                    'controller' => 'template',
//                    'action'     => 'attributes',
//                    'param'      => 'template',
//                    'value'      => $template->getId(),
//                    'param2'     => 'attributegroup',
//                    'value2'     => $attributeGroup->getId()
//                )),
//                $name,
//                'pane-navi-link attribute-group',
//                array(
//                    'data-pane-title' => ''
//                ),
//                null
//            );

            $attributeGroupsList[] = $data;
        }

        return $attributeGroupsList;
    }


    /**
     * categoryAssignmentAction Action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function categoryAssignmentAction() {

        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        // check access via ACL
        if (!$this->checkAccess($this->auth->getIdentity(), self::AREA, 'edit', $view)) {
            return $view;
        }

        try {

            if ($this->getRequest()->isPost()) {

                $categoryModel = new Model\CategoryModel($this->getServiceLocator(), $this->logger);
                $templateModel = new Model\TemplateModel($this->getServiceLocator(), $this->logger);

                // get data
                $data = $this->getRequest()->getPost();

                $templateId = (int)$this->params()->fromRoute('value');
                $categoryId = (int)$this->params()->fromRoute('value2');

                /* @var $category Entity\CategoryEntity */
                $category = $categoryModel->findById($categoryId);

                if (!$category->getTemplate()) {
                    /* @var $template Entity\TemplateEntity */
                    $template = $templateModel->findById($templateId);
                    $category->setTemplate($template);
                } else {
                    $category->setTemplate(null);
                }

                // update
                $categoryModel->update($category);
            }
        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
    }

    /**
     * AttributeGroupAssignment Action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function attributeGroupAssignmentAction() {

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
                $templateAttributeGroupModel  = new Model\TemplateAttributeGroupModel($this->getServiceLocator(), $this->logger);
                $templateModel                = new Model\TemplateModel($this->getServiceLocator(), $this->logger);

                // get data
                $data = $this->getRequest()->getPost();

                $templateId       = (int)$this->params()->fromRoute('value');
                $attributeGroupId = (int)$this->params()->fromRoute('value2');

                /* @var $attributeGroup Entity\AttributeGroupEntity */
                $attributeGroup = $attributeGroupModel->findById($attributeGroupId);
                /* @var $template Entity\TemplateEntity */
                $template = $templateModel->findById($templateId);

                // get entitiy
                $templateAttributeGroup = $templateAttributeGroupModel->findOneBy(array(
                    'template'       => $templateId,
                    'attributeGroup' => $attributeGroupId
                ));

                if ($templateAttributeGroup) {

                    // remove
                    $template->removeTemplateAttributeGroups(
                        new ArrayCollection(array($templateAttributeGroup))
                    );

                } else {

                    // create a new assignment
                    /* @var $templateAttributeGroup Entity\AttributeGroupAttributeEntity */
                    $templateAttributeGroup = new Entity\TemplateAttributeGroupEntity();
                    $templateAttributeGroup->setTemplate($template);
                    $templateAttributeGroup->setAttributeGroup($attributeGroup);
                    $templateAttributeGroup->setSort(0);

                    // add assignment
                    $template->addTemplateAttributeGroups(
                        new ArrayCollection(array($templateAttributeGroup))
                    );
                }

                // update
                $templateModel->update($template);
            }
        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
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
            $param      = $this->params()->fromRoute('param');
            $templateId = (int)$this->params()->fromRoute('value');

            // get model(s)
            $templateModel  = new Model\TemplateModel($this->getServiceLocator(), $this->logger);
            $categoryModel  = new Model\CategoryModel($this->getServiceLocator(), $this->logger);

            // get entities
            /* @var $template Entity\UserEntity */
            $template      = $templateModel->findById($templateId);

            // prepare form
            $form  = new Form\TemplateForm('form-attribute', array(), $templateModel->getEntityManager());
            if ($template) {
                //user exist, get data
                $urlParams = array(
                    'controller' => 'template',
                    'action'     => 'form',
                    'param'      => 'id',
                    'value'      => $template->getId()
                );

                $formSortEntities = $this->_getFormSortEntities($template);

            } else {
                // create new entity
                $template = new Entity\TemplateEntity();
                $urlParams = array(
                    'controller' => 'template',
                    'action'     => 'form'
                );
            }

            $form->setAttribute('action', $this->url()->fromRoute('home/default', $urlParams));
            //$templateModel->createMissingTranslations($template, 'Ffb\Backend\Entity\TemplateLangEntity');
            $form->bind($template);

            // check post
            if ($this->getRequest()->isPost()) {

                // get data
                $data = $this->getRequest()->getPost();

                // assign to form, to valiadate
                $form->setData($data);

                if ($form->isValid()) {

                    $template = $form->getData();

                    if ($template->getId()) {
                        // update user
                        $templateModel->update($template);
                    } else {
                        // insert user
                        $templateModel->insert($template);
                    }

                    $this->flashMessenger()->addMessage($this->translator->translate('MSG_FORM_UPDATED'));

//                    $view->setVariable('subnaviUrl', $this->url()->fromRoute('home/default', array(
//                        'controller' => 'attribute',
//                        'action'     => 'subnavi'
//                    )));
                    $view->setVariable('templateUrl', $this->url()->fromRoute('home/default', array(
                        'controller' => 'template',
                        'action'     => 'subnavi',
                        'param'      => 'template',
                        'value'      => $template->getId()
                    )));

                } else {
                    $this->flashMessenger()->addMessage($this->translator->translate('MSG_FORM_INVALID'));
                    $view->setVariable('state', 'error');
                    $view->setVariable('invalidFields', $form->getInvalidFields());
                }

            } else {

                if ($template->getId()) {
                    $view->setVariables(array(
                        'formSortEntities' => $template->getId() ? $formSortEntities->prepare() : null,
                        'categoryList'     => $template->getId() ? $categoryModel->getCategoryTree($template) : array()
                    ));
                }

                $view->setVariables(array(
                    'form'             => $form->prepare(),
                ));
            }
        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
    }

    /**
     * Delete template
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
            $templateId = $this->params()->fromRoute('value', null);

            // get model(s)
            $templateModel = new Model\TemplateModel($this->getServiceLocator(), $this->logger);

            // get entities
            /* @var $template Entity\TemplateEntity */
            $template = $templateModel->findById($templateId);

            // check post
            if ($this->getRequest()->isPost()) {

                // delete user
                $templateModel->delete($template);

                $attributeGroups = $templateModel->findAll();
                if (count($attributeGroups) > 0) {
                    $template = $attributeGroups[0];
                    $view->setVariable('callBackUrl', $this->url()->fromRoute('home/default', array(
                        'controller' => 'template',
                        'action'     => 'subnavi',
                        'param'      => 'template',
                        'value'      => $template->getId()
                    )));
                }

                $this->flashMessenger()->addMessage($this->translator->translate('MSG_TEMPLATE_DELETED'));
            }

        } catch (\Exception $ex) {

            //$this->_displayException($view, $ex);

            $view->setVariable('state', 'error');
            $this->flashMessenger()->addMessage($this->translator->translate('MSG_TEMPLATE_CAN_NOT_BE_DELETED'));
        }

        return $view;
    }

    /**
     * Copy template
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
            $templateId = $this->params()->fromRoute('value', null);

            // get model(s)
            $templateModel = new Model\TemplateModel($this->getServiceLocator(), $this->logger);

            // get entities
            /* @var $template Entity\TemplateEntity */
            $template = $templateModel->findById($templateId);

            // check post
            if ($this->getRequest()->isPost()) {

                // clone
                $templateClone = clone $template;

                // set new name
                $templateClone->setName($this->translator->translate('LBL_COPY_PREFIX') . $templateClone->getName());

                // persist
                $templateModel->insert($templateClone);

                $view->setVariable('callBackUrl', $this->url()->fromRoute('home/default', array(
                    'controller' => 'template',
                    'action'     => 'subnavi',
                    'param'      => 'template',
                    'value'      => $templateClone->getId()
                )));

                $this->flashMessenger()->addMessage($this->translator->translate('MSG_TEMPLATE_COPIED'));
            }

        } catch (\Exception $ex) {

            //$this->_displayException($view, $ex);

            $view->setVariable('state', 'error');
            $this->flashMessenger()->addMessage($this->translator->translate('MSG_TEMPLATE_CAN_NOT_BE_COPIED'));
        }

        return $view;
    }

    /**
     * atributes Action
     *
     * @return ZendModel\ViewModel
     */
    public function attributesAction() {

        if (!$this->_isAjax) {
            return $this->_redirectNonAjax();
        }

        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        try {

            // check access via ACL
            if (!$this->checkAccess($this->auth->getIdentity(), self::AREA, null, $view)) {
                return $view;
            }

            // get param(s)
            $templateId = $this->params()->fromRoute('value', null);
            $attributeGroupId = $this->params()->fromRoute('value2', null);

            // get model(s)
            $templateModel = new Model\TemplateModel($this->getServiceLocator(), $this->logger);
            $attributeGroupModel = new Model\AttributeGroupModel($this->getServiceLocator(), $this->logger);

            // get entities
            /* @var $attributeGroup Entity\AttributeGroupEntity */
            $attributeGroup = $attributeGroupModel->findById($attributeGroupId);

            $view->setVariables(array(
                'attributeGroupAttributes' => $attributeGroup->getAttributeGroupAttributes()
            ));
        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
    }

    /**
     * Sort attribute group attributes
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function sortAttributeGroupsAction() {

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

                $templateId = $this->params()->fromRoute('value');

                $templateModel = new Model\TemplateModel($this->getServiceLocator(), $this->logger);
                $template      = $templateModel->findById($templateId);

                // form
                $form = $this->_getFormSortEntities($template);
                $form->bind($template);

                // get data
                $data = $this->getRequest()->getPost();

                // assign to form, to validate
                $form->setData($data);

                if ($form->isValid()) {

                    $template = $form->getData();

                    if ($template->getId()) {
                        // update
                        $templateModel->update($template);
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
     * get template attribute group sortable form
     *
     * @param Entity\TemplateEntity $template
     *
     * @return Form\SortEntitiesForm
     */
    private function _getFormSortEntities(Entity\TemplateEntity $template) {

        $templateModel = new Model\TemplateModel($this->getServiceLocator(), $this->logger);

        $urlParams = array(
            'controller' => 'template',
            'action'     => 'sortAttributeGroups',
            'param'      => 'template',
            'value'      => $template->getId()
        );

        // attributeGroupForm
        $options = array(
            'fieldsetName'        => 'TemplateAttributeGroupFieldset',
            'collectionFieldName' => 'templateAttributeGroups'
        );
        $form    = new Form\SortEntitiesForm('form-sort-entities', $options, $templateModel->getEntityManager());
        $form->setAttribute('action', $this->url()->fromRoute('home/default', $urlParams));
        $form->setAttribute('class', $form->getAttribute('class') . ' form-sort-entities');
        $form->bind($template);

        return $form;
    }

}
