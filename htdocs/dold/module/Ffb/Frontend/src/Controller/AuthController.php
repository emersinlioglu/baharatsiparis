<?php

namespace Ffb\Frontend\Controller;

use Ffb\Backend\Authentication\BackendAdapter;
use Ffb\Backend\Entity;
use Ffb\Backend\Form;
use Ffb\Backend\View\Helper;

use Zend\Authentication\AuthenticationService;
use Zend\View\Model;
use Zend\Json\Json;

/**
 * @author erdal.mersinlioglu
 */
class AuthController extends AbstractFrontendController {

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
     * Check authentication.
     * Is empty in order to prevents the authentication check.
     * The login and logout actions must not depend on a loggedin user!
     */
    public function checkAuthentication() {
        // NOOP; this empty method prevents the authentication check
    }

    /**
     * Display the login form.
     * On a POST request the login process is performed. This is done via the
     * BackendAdapter.
     *
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function loginAction() {

        $view = new Model\ViewModel();
        $this->layout('layout/layout_empty');

        // build form
        $form = new Form\LoginForm();
        $form->setAttribute('action', $this->url()->fromRoute('home/default', array(
            'controller' => 'auth',
            'action'     => 'login'
        )));

        $form->get('login')->setValue($this->translator->translate($form->get('login')->getValue()));

        $view->setVariable('form', $form);

        // perform login (via authentication service)
        if ($this->getRequest()->isPost()) {

            // get credentials from request
            $username = $this->getRequest()->getPost('email');
            $password = $this->getRequest()->getPost('password');

            // create auth service & adapter
            $authService = new AuthenticationService();
            $authAdapter = new BackendAdapter($this->getServiceLocator(), $username, $password);

            // perform authentication
            // auth service is responsible for persistent storage of identity
            $authResult = $authService->authenticate($authAdapter);

            /*  @var $identity UserEntity */
            $identity = $authResult->getIdentity();
            // handle auth result
            if ($authResult->isValid()) {

                // set default content lang
                $session = $this->getSession();
                $session->offsetSet('contentLang', Entity\LangEntity::DEFAULT_LANGUAGE_CODE);

                // perform redirect
                $view->setVariable('state', 'ok');
                if ($this->getRequest()->isXmlHttpRequest()) {
                    $view->setVariable('redirect', $this->url()->fromRoute('home', array(
                        'controller' => 'import',
                        'action'     => 'index'
                    )));

                    return $view;
                } else {
                    return $this->redirect()->toRoute('home', array(
                        'controller' => 'import',
                        'action'     => 'index'
                    ));
                }
            } else {

                // user could not be authenticated
                // thus display form again (w/ message)
                $view->setVariable('state', 'error');
                $this->flashMessenger()->addMessage($this->translator->translate('MSG_WRONG_LOGIN_DATA'));
                foreach ($authResult->getMessages() as $message) {
                    $this->flashMessenger()->addMessage($message);
                }

                return $view;
            }

            $form->bind(new Entity\UserEntity());
            $form->setData($this->getRequest()->getPost()->toArray());
        }

        return $view;
    }

    /**
     * Perform logout.
     * After logout the user is redirected to the login form.
     */
    public function logoutAction() {

        // create auth service & adapter
        $authService = new AuthenticationService();

        // clearing identity means logging out
        $authService->clearIdentity();

        // redirect to login form
        return $this->redirect()->toRoute('home/default', array(
            'controller' => 'auth',
            'action'     => 'login'
        ));
    }

}