<?php

namespace Ffb\Backend\Controller;

use \Ffb\Backend\View\Helper;
use \Ffb\Backend\Model;
use \Ffb\Backend\Form;
use \Ffb\Backend\Entity;

use Zend\Json\Json;
use Zend\View\Model as ZendModel;

/**
 *
 * @author erdal.mersinlioglu
 */
class AdminController extends AbstractBackendController {

    /**
     * Area name for the rights
     * @var string
     */
    const AREA = 'admin';

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
            'TTL_DELETE_USER'
        ));
    }

    /**
     * indexAction
     *
     * @return ZendModel\ViewModel
     * @todo add documentation
     */
    public function indexAction() {

        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        try {

            //// check acl access
            //$isAllowed = $this->checkAccess($this->auth->getIdentity()->getRole(), 'admin', 'index', $view);
            //if ($isAllowed !== true) {
            //    return $isAllowed;
            //}

            // get main navi list
            $linkedlistHelper = new Helper\HtmlLinkedListHelper();
            $linkHelper       = new Helper\HtmlLinkHelper();

            // prepare main navi
            $mainLinks = array(
                $linkHelper->getHtml(
                    $this->translator->translate('TTL_USERS'),
                    $this->url()->fromRoute('home/default', array(
                        'controller' => 'admin',
                        'action'     => 'users'
                    )),
                    $this->translator->translate('TTL_USERS'),
                    'pane-navi-link users',
                    array(
                        'data-pane-title' => '', //$this->translator->translate('TTL_USERS')
                    )
                ),
                //$linkHelper->getHtml(
                //    $this->translator->translate('TTL_LANGUAGES'),
                //    $this->url()->fromRoute('home/default', array(
                //        'controller' => 'admin',
                //        'action'     => 'languages'
                //    )),
                //    $this->translator->translate('TTL_LANGUAGES'),
                //    'pane-navi-link languages',
                //    array(
                //        'data-pane-title' => $this->translator->translate('TTL_LANGUAGES')
                //    )
                //),
                $linkHelper->getHtml(
                    $this->translator->translate('TTL_ADMIN_EXPORT'),
                    $this->url()->fromRoute('home/default', array(
                        'controller' => 'admin',
                        'action'     => 'export'
                    )),
                    $this->translator->translate('TTL_ADMIN_EXPORT'),
                    'pane-navi-link export',
                    array(
                        'data-pane-title' => $this->translator->translate('TTL_ADMIN_EXPORT')
                    )
                ),
                $linkHelper->getHtml(
                    $this->translator->translate('TTL_ADMIN_ERRORLOG'),
                    $this->url()->fromRoute('home/default', array(
                        'controller' => 'admin',
                        'action'     => 'errorlog'
                    )),
                    $this->translator->translate('TTL_ADMIN_ERRORLOG'),
                    'pane-navi-link errorlog',
                    array(
                        'data-pane-title' => '' /*$this->translator->translate('TTL_ADMIN_ERRORLOG')*/
                    )
                )
            );

            // set variables
            $view->setVariables(array(
                'uriGetList'      => $this->url()->fromRoute('home/default', array(
                    'controller' => 'admin',
                    'action'     => 'index'
                )),
                'withSubnavi'     => true,
                'paneFirstTitle'  => '&nbsp;',
                'paneSecondTitle' => '&nbsp;',
                'paneSecondContent' => '',
                'entitiesList'    => $linkedlistHelper->getHtml($mainLinks, 'ffb-accordion navi main-navi-list simple')
            ));

        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
    }

    /**
     * usersAction
     *
     * @return ZendModel\ViewModel
     * @todo add documentation
     */
    public function usersAction() {

        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        try {

            //// check access via ACL
            //if (!$this->checkAccess($this->auth->getIdentity()->getRole(), 'user', 'index', $view)) {
            //    return $view;
            //}

            // get model(s)
            $userModel = new Model\UserModel($this->getServiceLocator());

            // get view helper
            $link   = new Helper\HtmlLinkHelper();

            // prepare items list
            $items  = array('values' => array());

            // create booking list
            /* @var $entity Entity\UserEntity */
            foreach ($userModel->findAll() as $entity) {

                $name  = $entity->getName() /* . ' (' . $entity->getRole() . ')' */;

                $items['values'][] = $link(
                    $name,
                    $this->url()->fromRoute('home/default', array(
                        'controller' => 'admin',
                        'action'     => 'userform',
                        'param'      => 'id',
                        'value'     => $entity->getId()
                    )),
                    $name,
                    'pane-navi-link',
                    array(
                        'data-pane-title' => $this->translator->translate('TTL_USER_DATA')
                    )
                );
            }

            $view->setVariable('items', $items);

            $view->setVariable('uriAddEntity', $this->url()->fromRoute('home/default', array(
                'controller' => 'admin',
                'action'     => 'userform'
            )));
        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
    }

    /**
     * userformAction
     *
     * @return ZendModel\ViewModel
     * @todo add documentation
     */
    public function userformAction() {

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
            $userId = (int)$this->params()->fromRoute('value');

            // get model(s)
            $userModel = new Model\UserModel($this->getServiceLocator(), $this->logger);

            // get entities
            /* @var $user Entity\UserEntity */
            $user = $userModel->findById($userId);

            // prepare form
            $form  = new Form\UserForm('form-user', array(), $userModel->getEntityManager());
            if ($user) {
                //user exist, get data
                $urlParams = array(
                    'controller' => 'admin',
                    'action'     => 'userform',
                    'param'      => 'id',
                    'value'      => $user->getId()
                );
            } else {

                //// check access via ACL
                //if (!$this->checkAccess($this->auth->getIdentity()->getRole(), 'user', 'form_create', $view)) {
                //    return $view;
                //}

                //user not exist create new
                $user = new Entity\UserEntity();

                $urlParams = array(
                    'controller' => 'admin',
                    'action'     => 'userform',
                );
            }
            $form->setAttribute('action', $this->url()->fromRoute('home/default', $urlParams));
            $form->bind($user);

            // check post
            if ($this->getRequest()->isPost()) {

                //// check access via ACL
                //if (!$this->checkAccess($this->auth->getIdentity()->getRole(), 'user', 'form_update', $view)) {
                //    return $view;
                //}

                // get data
                $data = $this->getRequest()->getPost();

                // assign to form, to valiadate
                $form->setData($data);

                if ($form->isValid()) {

                    $user = $form->getData();

                    // check another user in same client or sysadmin with email
                    if (!$userModel->isEmailUnique($user)) {

                        $this->flashMessenger()->addMessage($this->translator->translate('MSG_EMAIL_EXIST'));
                        $view->setVariable('state', 'error');
                        return $view;
                    }

                    // new password
                    if (isset($data['newPassword']) && strlen($data['newPassword']) > 0) {

                        $newPassword = $data['newPassword'];
                        $user->setPassword($newPassword);
                    }

                    if ($user->getId()) {
                        // update user
                        $userModel->update($user);
                    } else {
                        // insert user
                        $userModel->insert($user);
                    }

                    $this->flashMessenger()->addMessage($this->translator->translate('MSG_FORM_UPDATED'));
                    $view->setVariable('naviUrl', $this->url()->fromRoute('home/default', array(
                        'controller' => 'admin',
                        'action'     => 'users'
                    )));
                    $view->setVariable('subnaviUrl', $this->url()->fromRoute('home/default', array(
                        'controller' => 'admin',
                        'action'     => 'userform',
                        'param'      => 'id',
                        'value'      => $user->getId()
                    )));

                } else {
                    $this->flashMessenger()->addMessage($this->translator->translate('MSG_FORM_INVALID'));
                    $view->setVariable('state', 'error');
                    $view->setVariable('invalidFields', $form->getInvalidFields());
                }

            } else {

                // delete url
                $form->get('delete')->setAttribute('data-url', $this->url()->fromRoute('home/default', array(
                    'controller' => 'admin',
                    'action' => 'deleteuser',
                    'param' => 'user',
                    'value' => $user->getId()
                )));

                $view->setVariables(array(
                    'form' => $form->prepare(),
                    'userExists' => $user->getId() > 0
                ));
            }
        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
    }

    /**
     * deleteuser
     *
     * @return ZendModel\ViewModel
     * @todo add documentation
     */
    public function deleteuserAction() {

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
            $userId = (int)$this->params()->fromRoute('value');

            // get model(s)
            $userModel = new Model\UserModel($this->getServiceLocator(), $this->logger);

            // get entities
            /* @var $user Entity\UserEntity */
            $user = $userModel->findById($userId);

            // check post
            if ($this->getRequest()->isPost()) {

                // delete user
                $userModel->delete($user);

                $this->flashMessenger()->addMessage($this->translator->translate('MSG_USER_DELETED'));
            }

        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
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
     * Encrypt password with salt from config
     *
     * @param string $password
     */
    protected function _encryptPassword($password) {

        $salt = $this->config['application']['acl']['passwordSalt'];
        $encrypted = sha1($salt . $password . $salt);

        return $encrypted;
    }

    /**
     * errorlogAction
     *
     * @return ZendModel\ViewModel
     */
    public function errorlogAction() {

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

            $errorlogPath = ini_get('error_log');

            $lines = "";
            $fp = fopen($errorlogPath, "r");
            $cnt = 0;
            while (!feof($fp)) {
                $line = fgets($fp, 4096);
                $lines .= $line;
                if ($cnt >= 499) {
                    break;
                }
                $cnt++;
            }
            fclose($fp);

            $view->setVariable('errorlog', $lines);


        } catch (\Exception $ex) {

            $this->_displayException($view, $ex);
        }

        return $view;
    }
}