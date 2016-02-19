<?php

namespace Ffb\Backend\Controller;

use DoctrineORMModule\Proxy\__CG__\Ffb\Backend\Entity\AttributeGroupEntity;
use Ffb\Backend\Model;
use Ffb\Backend\Entity;
use Ffb\Backend\Form;
use Ffb\Backend\Service;
use Ffb\Backend\View\Helper;

use Zend\Json\Json;
use Zend\View\Model as ZendModel;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author erdal.mersinlioglu
 */
class AttributeController extends AbstractBackendController {

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
     * Controller translations
     *
     * @return array
     */
    protected function _getControllerTranslations() {

        return array_merge(parent::_getControllerTranslations(), array(
            'TTL_ERROR',
            'TTL_SAVE_ATTRIBUTE',
            'TTL_SAVE_TEMPLATE'
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

        // GET Request
        $view->setVariables(array(
            'uriGetList'        => $this->url()->fromRoute('home/default', array(
                'controller' => 'attribute',
                'action'     => 'index'
            )),
            'withSubnavi'       => true,
            'paneFirstTitle'    => '&nbsp;',
            'paneSecondTitle'   => '&nbsp;',
            'paneSecondContent' => $this->_getSubNavigationHtml(),
            //'entityList'    => $linkedlistHelper->getHtml($mainLinks, 'ffb-accordion navi main-navi-list simple'),
            'tabs'              => $this->_getTabs()
        ));

        if ($this->getRequest()->isPost()) {

            // POST Request
            $smartyRenderer = $this->getServiceLocator()->get('SmartyRenderer');
            $viewModel      = new ZendModel\ViewModel();
            $viewModel->setTemplate('ffb\backend/controller/attribute/index');
            $viewModel->setVariables($view->getVariables());

            $view->setVariables(array(
                'entityList' => $smartyRenderer->render($viewModel)
            ));
        }

        return $view;
    }

    /**
     * Returns tabs
     *
     * @param array $param
     *
     * @return array
     */
    private function _getTabs(array $param = null) {

        $userModel = new Model\UserModel($this->getServiceLocator(), $this->logger);
        $identity = $this->auth->getIdentity();
        /* @var $user Entity\UserEntity */
        $user = $userModel->findById($identity->getId());

        //$linkedlistHelper = new Helper\HtmlLinkedListHelper();
        $linkHelper            = new Helper\HtmlLinkHelper();
        $addAttributeGroupLink = $linkHelper->getHtml(
            '',
            $this->url()->fromRoute('home/default', array(
                'controller' => 'attributegroup',
                'action'     => 'form',
            )),
            '',
            'button gray add attribute-group'
        );

        $addTemplateLink = $linkHelper->getHtml(
            '',
            $this->url()->fromRoute('home/default', array(
                'controller' => 'template',
                'action'     => 'form',
            )),
            '',
            'button gray add template'
        );

        $tabs = array();

        // attribute tab
        if ($user->getAllowAttributes()) {
            $tabs[] = array(
                'label'            => $this->translator->translate('TTL_ATTRIBUTE'),
                'active'           => true,
                'type'             => 'attribute-groups',
                'items'            => $this->_getAttributeGroupsList(),
                'itemListTitleKey' => 'TTL_ATTRIBUTE_GROUPS',
                'uriAddEntity'     => $addAttributeGroupLink
            );
        }

        // template tab
        if ($user->getAllowTemplates()) {
            $tabs[] = array(
                'label'            => $this->translator->translate('TTL_TEMPLATE'),
                'active'           => false,
                'type'             => 'templates',
                'items'            => $this->_getTemplatesList(),
                'itemListTitleKey' => 'TTL_TEMPLATE',
                'uriAddEntity'     => $addTemplateLink
            );
        }

        return $tabs;
    }

    /**
     * Returns the attribute groups list
     *
     * @return array
     */
    private function _getAttributeGroupsList() {

        $linkHelper = new Helper\HtmlLinkHelper();
        $spanHelper = new Helper\HtmlSpanHelper();
        $result     = array();

        $attributeGroupModel = new Model\AttributeGroupModel($this->getServiceLocator(), $this->logger);

        foreach ($attributeGroupModel->findAll() as $attrGroup) {
            /* @var $attrGroup \Ffb\Backend\Entity\AttributeGroupEntity */
            /* @var $trans \Ffb\Backend\Entity\AttributeGroupLangEntity */
            $trans = $attrGroup->getCurrentTranslation();

            $link = $linkHelper->getHtml(
                $trans->getName(),
                $this->url()->fromRoute('home/default', array(
                    'controller' => 'attribute',
                    'action'     => 'subnavi',
                    'param'      => 'attributegroup',
                    'value'      => $attrGroup->getId()
                )),
                $trans->getName(),
                'pane-navi-link attributes',
                array(
                    'data-pane-title' => '&nbsp;',
                    'data-copy-url'   => $this->url()->fromRoute('home/default', array(
                        'controller' => 'attributegroup',
                        'action'     => 'copy',
                        'param'      => 'attributegroup',
                        'value'      => $attrGroup->getId()
                    )),
                    'data-delete-url' => $this->url()->fromRoute('home/default', array(
                        'controller' => 'attributegroup',
                        'action'     => 'delete',
                        'param'      => 'attributegroup',
                        'value'      => $attrGroup->getId()
                    )),
                    'data-form-url'   => $this->url()->fromRoute('home/default', array(
                        'controller' => 'attributegroup',
                        'action'     => 'form',
                        'param'      => 'attributegroup',
                        'value'      => $attrGroup->getId()
                    ))
                )
            );

            $span = $spanHelper->getHtml(
                '',
                'edit',
                array(
                    'data-form-url' => $this->url()->fromRoute('home/default', array(
                        'controller' => 'attributegroup',
                        'action'     => 'form',
                        'param'      => 'attributegroup',
                        'value'      => $attrGroup->getId()
                    ))
                )
            );

            $result[] = $span . $link;
        }

        return $result;
    }

    /**
     * Returns the templates list
     *
     * @return array
     */
    private function _getTemplatesList() {

        // get model(s)
        $templateModel = new Model\TemplateModel($this->getServiceLocator(), $this->logger);
        $linkHelper    = new Helper\HtmlLinkHelper();
        $spanHelper    = new Helper\HtmlSpanHelper();

        $result = array();
        foreach ($templateModel->findAll() as $template) {

            $link = $linkHelper->getHtml(
                $template->getName(),
                $this->url()->fromRoute('home/default', array(
                    'controller' => 'template',
                    'action'     => 'subnavi',
                    'param'      => 'template',
                    'value'      => $template->getId()
                )),
                $template->getName(),
                'pane-navi-link template',
                array(
                    'data-pane-title' => '&nbsp;', //$this->translator->translate('TTL_ATTRIBUTE_GROUP'),
                    'data-delete-url' => $this->url()->fromRoute('home/default', array(
                        'controller' => 'template',
                        'action'     => 'delete',
                        'param'      => 'template',
                        'value'      => $template->getId()
                    )),
                    'data-copy-url'   => $this->url()->fromRoute('home/default', array(
                        'controller' => 'template',
                        'action'     => 'copy',
                        'param'      => 'template',
                        'value'      => $template->getId()
                    )),
                    'data-form-url'   => $this->url()->fromRoute('home/default', array(
                        'controller' => 'template',
                        'action'     => 'form',
                        'param'      => 'template',
                        'value'      => $template->getId()
                    ))
                )
            );

            $span = $spanHelper->getHtml(
                '',
                'edit',
                array(
                    'data-form-url' => $this->url()->fromRoute('home/default', array(
                        'controller' => 'template',
                        'action'     => 'form',
                        'param'      => 'template',
                        'value'      => $template->getId()
                    ))
                )
            );

            $result[] = $span . $link;
        }

        return $result;
    }

    /**
     * Returns search form
     * @param arrary $data Postdata
     * @return \Ffb\Backend\Form\SearchForm
     */
    private function _getSearchForm($attributeGroupId = null, array $data = null) {

        $form = new Form\SearchForm('search-attribute-subnavi');
        $form->setAttribute('class', 'form-default form-search-subnavi form-search-attribute-subnavi');
        $form->setAttribute('action', $this->url()->fromRoute('home/default', array(
            'controller' => 'attribute',
            'action'     => 'subnavi',
            'param'      => 'attributegroup',
            'value'      => $attributeGroupId
        )));

        if (!is_null($data)) {
            $form->setData($data);
        }

        return $form->prepare();
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function subnaviAction() {

//        $view = new ZendModel\ViewModel(array(
//            'state' => 'ok'
//        ));

        // get param(s)
        $attributeGroupId = $this->params()->fromRoute('value', null);

        $data = $this->getRequest()->getPost()->toArray();

        // get viewModel
        $view = $this->_getSubnavigationViewModel($attributeGroupId, $data);

        if ($this->getRequest()->isPost()) {
            // POST Request
        } else {
            // GET Request
        }

        return $view;
    }

    /**
     * @param null $attributeGroupId
     *
     * @return mixed
     */
    protected function _getSubNavigationHtml($attributeGroupId = null) {

        // render cart details
        $smartyRenderer = $this->getServiceLocator()->get('SmartyRenderer');
        $viewModel = $this->_getSubnavigationViewModel();

        return $smartyRenderer->render($viewModel);
    }

    /**
     * Returns the viewModel für subnavigation
     *
     * @param string $attributeGroupId
     * @param array $data
     * @return \Zend\View\Model\ViewModel
     */
    private function _getSubnavigationViewModel($attributeGroupId = null, array $data = null) {

        $attributeList = $this->_getAttributesList($attributeGroupId, $data);

        $viewModel      = new ZendModel\ViewModel();
        $viewModel->setTemplate('ffb\backend/controller/attribute/subnavi');
        $viewModel->setVariables(array(
            'attributes'        => $attributeList,
            'showAttributeLink' => true,
            'uriAddEntity'      => $this->url()->fromRoute('home/default', array(
                'controller' => 'attribute',
                'action'     => 'form'
            )),
            'withSubnavi'       => true,
            'form' => $this->_getSearchForm($attributeGroupId, $data)
        ));

        return $viewModel;
    }

    /**
     * @param int $attributeGroupId
     * @param array $data
     * @return array
     */
    private function _getAttributesList($attributeGroupId, array $data = null) {

        $data = is_null($data) ? array() : $data;

        // get model(s)
        $attributeModel      = new Model\AttributeModel($this->getServiceLocator(), $this->logger);
        $attributeGroupModel = new Model\AttributeGroupModel($this->getServiceLocator(), $this->logger);

        $attributes = $attributeModel->findForSubnavi($data);

        $attributeList  = array();
        $linkHelper     = new Helper\HtmlLinkHelper();
        $checkboxHelper = new \Zend\Form\View\Helper\FormCheckbox();

        // checkbox
        $checkbox = new \Zend\Form\Element\Checkbox('assigned');
        //$checkbox->setUseHiddenElement(true);
        $checkbox->setCheckedValue(1);
        $checkbox->setUncheckedValue(0);

        /*  @var $attribute Entity\AttributeEntity */
        foreach ($attributes as $attribute) {

            $name = $attribute->getCurrentTranslation()->getName();

            $isAssigned = false;
            /*@var $attributeGroupAttribute Entity\AttributeGroupAttributeEntity */
            foreach ($attribute->getAttributeGroupAttributes() as $attributeGroupAttribute) {
                $isAssigned = $attributeGroupId == $attributeGroupAttribute->getAttributeGroup()->getId();
                if ($isAssigned) {
                    break;
                }
            }

            // set checkbox value for isAssigned and add attribute id for better selection in js
            $checkbox->setValue($isAssigned);
//            $checkbox->setAttribute('data-attribute-id', $attribute->getId());
            $checkbox->setAttribute('data-href', $this->url()->fromRoute('home/default', array(
                'controller' => 'attributeGroup',
                'action'     => 'attributeAssignment',
                'param'      => 'attribute',
                'value'      => $attribute->getId(),
                'param2'     => 'attributegroup',
                'value2'     => $attributeGroupId
            )));

            // render checkbox
            $liString = '';
            if ($attributeGroupId > 0) {
                $liString = $checkboxHelper->render($checkbox);
            }

            // render link
            $liString .= $linkHelper->getHtml(
                $name,
                $this->url()->fromRoute('home/default', array(
                    'controller' => 'attribute',
                    'action'     => 'form',
                    'param'      => 'attribute',
                    'value'      => $attribute->getId(),
                    'param2'     => 'attributegroup',
                    'value2'     => $attributeGroupId
                )),
                $name,
                'pane-navi-link attribute',
                array(
                    'data-pane-title' => '',
                    'data-delete-url' => $this->url()->fromRoute('home/default', array(
                        'controller' => 'attribute',
                        'action'     => 'delete',
                        'param'      => 'attribute',
                        'value'      => $attribute->getId()
                    ))
                ),
                null
            );

            $attributeList[] = $liString;
        }

        return $attributeList;
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
            $params           = $this->getUrlParamsForFormAction();
            $attributeGroupId = $params['attributeGroupId'];
            $attributeId      = $params['attributeId'];

            // get model(s)
            $attributeModel      = new Model\AttributeModel($this->getServiceLocator(), $this->logger);
            $attributeGroupModel = new Model\AttributeGroupModel($this->getServiceLocator(), $this->logger);

            // get entities
            /* @var $attribute Entity\UserEntity */
            $attribute       = $attributeModel->findById($attributeId);
            $attributeGroup  = $attributeGroupModel->findById($attributeGroupId);

            // forms
            $form = $this->_getAttributeForm($attribute, $attributeGroup);

            // check post
            if ($this->getRequest()->isPost()) {

                // get data
                $data = $this->getRequest()->getPost();

                // assign to form, to validate
                $form->setData($data);

                if ($form->isValid()) {

                    $attribute = $form->getData();

                    if ($attribute->getId()) {
                        // update user
                        $attributeModel->update($attribute);
                    } else {
                        // insert user
                        $attributeModel->insert($attribute);
                    }

                    $this->flashMessenger()->addMessage($this->translator->translate('MSG_FORM_UPDATED'));

                    $view->setVariable('subnaviUrl', $this->url()->fromRoute('home/default', array(
                        'controller' => 'attribute',
                        'action'     => 'subnavi'
                    )));

                    $urlOptions = array(
                        'controller' => 'attribute',
                        'action'     => 'form',
                        'param'      => 'attribute',
                        'value'      => $attribute->getId(),
                        'param2'     => 'attributegroup'
                    );
                    if ($attributeGroup) {
                        $urlOptions['value2'] = $attributeGroup->getId();
                    }
                    $view->setVariable('attributeUrl', $this->url()->fromRoute('home/default', $urlOptions));
                } else {
                    $this->flashMessenger()->addMessage($this->translator->translate('MSG_FORM_INVALID'));
                    $view->setVariable('state', 'error');
                    $view->setVariable('invalidFields', $form->getInvalidFields());
                }
            } else {

                // set attribute types
                $form->get('type')->setValueOptions($attributeModel->getAttributeTypeValueOptions());

                $view->setVariables(array(
                    'form'               => $form->prepare()
                ));
            }
        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
    }

    /**
     * Returns attribute form
     *
     * @param Entity\AttributeEntity|null      $attribute
     * @param Entity\AttributeGroupEntity|null $attributeGroup
     *
     * @return Form\AttributeForm
     */
    protected function _getAttributeForm(
        Entity\AttributeEntity $attribute = null,
        Entity\AttributeGroupEntity $attributeGroup = null) {

        $attributeModel = new Model\AttributeModel($this->getServiceLocator(), $this->logger);

        // prepare forms
        $form = new Form\AttributeForm('form-attribute', array(), $attributeModel->getEntityManager());
        $attributeModel->createMissingTranslations($attribute, 'Ffb\Backend\Entity\AttributeLangEntity');

        $urlParams = array(
            'controller' => 'attribute',
            'action'     => 'form',
            'param'      => 'attribute',
            'value'      => '0',
            'param2'     => 'attributegroup',
            'value2'     => '0'
        );
        if ($attributeGroup) {
            $urlParams['value2'] = $attributeGroup->getId();
        }

        if ($attribute) {
            //attribute exist, get data
            $urlParams['value'] = $attribute->getId();
        } else {
            // create new attribute
            $attribute = new Entity\AttributeEntity();

            // add AttributeGroupAttribute, if attributeGroupId is given
            if ($attributeGroup) {
                $attributeGroupAttribute = new Entity\AttributeGroupAttributeEntity();
                $attributeGroupAttribute->setAttributeGroup($attributeGroup);
                $attribute->addAttributeGroupAttributes(new ArrayCollection(array($attributeGroupAttribute)));
            }
        }

        $form->setAttribute('action', $this->url()->fromRoute('home/default', $urlParams));
        $attributeModel->createMissingTranslations($attribute, 'Ffb\Backend\Entity\AttributeLangEntity');
        $form->bind($attribute);

        return $form;
    }

    /**
     * get attribute id and attributegroup id from Url
     *
     * @return array
     */
    private function getUrlParamsForFormAction() {

        $param      = $this->params()->fromRoute('param');
        $param2     = $this->params()->fromRoute('param2');
        $value      = $this->params()->fromRoute('value', 0);
        $value2     = $this->params()->fromRoute('value2', 0);

        $attributeId = $attributeGroupId = null;
        if ($param == "attribute") {
            $attributeId = $value;
            $attributeGroupId = $value2;
        } else if ($param == "attributegroup") {
            $attributeId = $value2;
            $attributeGroupId = $value;
        }

        return array(
            'attributeId'      => $attributeId,
            'attributeGroupId' => $attributeGroupId
        );
    }

    /**
     * delete action
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
            $attributeId = (int)$this->params()->fromRoute('value');

            // get model(s)
            $attributeModel = new Model\AttributeModel($this->getServiceLocator(), $this->logger);

            // get entities
            /* @var $attribute Entity\ProductEntity */
            $attribute = $attributeModel->findById($attributeId);

            // check post
            if ($this->getRequest()->isPost()) {

                // delete
                $attributeModel->delete($attribute);

                //// to refresh the navigation
                //$view->setVariable('callBackUrl', $this->url()->fromRoute('home/default', array(
                //    'controller' => 'attribute',
                //    'action' => 'subnavi',
                //    'param' => 'attribute',
                //    'value' => $categoryId
                //)));

                $this->flashMessenger()->addMessage($this->translator->translate('MSG_PRODUCT_DELETED'));
            }

        } catch (\Exception $e) {

            //$this->_displayException($view, $e);

            $view->setVariable('state', 'error');
            $this->flashMessenger()->addMessage($this->translator->translate('MSG_PRODUCT_CAN_NOT_BE_DELETED'));
        }

        return $view;
    }
}