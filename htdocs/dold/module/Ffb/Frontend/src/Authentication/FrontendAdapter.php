<?php

namespace Ffb\Frontend\Authentication;

use Doctrine\ORM\EntityManager;
//use DoctrineModule\Options\Authentication as DoctrineAuthentication;

use Ffb\Backend\Entity\LangEntity;
use Ffb\Frontend\Model;

use Zend\Authentication as ZendAuthentication;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

/**
 * How to check the login:
 * $auth = new \Zend\Authentication\AuthenticationService();
 * if ($auth->hasIdentity()) $identity = $auth->getIdentity();
 *
 * @author marcus.gnass
 */
class FrontendAdapter implements ZendAuthentication\Adapter\AdapterInterface {

    /**
     * @var ServiceLocatorInterface
     */
    private $_sl;

    /**
     * @var string
     */
    private $_username;

    /**
     * @var string
     */
    private $_password;

    /**
     * @var string
     */
    private $_defaultLanguage;

    /**
     * Sets serviceLocator & credentials for authentication.
     *
     * @param ServiceLocatorInterface $sl
     * @param string                  $username
     * @param string                  $password
     */
    public function __construct(ServiceLocatorInterface $sl, $username, $password, $defaultLanguage = '') {

        $this->_sl              = $sl;
        $this->_username        = $username;
        $this->_password        = $password;
        $this->_defaultLanguage = $defaultLanguage;
    }

    /**
     * Performs an authentication attempt.
     * The returned authentication result object contains the user object as
     * identity.
     *
     * @return AuthResult
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If
     *         authentication cannot be performed
     */
    public function authenticate() {

        // get config & values
        $modConf = $this->_sl->get('Config');

        // get model(s)
        $userModel = new Model\UserModel($this->_sl);

        // get entities
        $users = $userModel->findByCredentials($this->_username, $this->_password);

        // assert that exactly one user was found that has the given credentials
        $messages = array();
        if (1 === count($users)) {

            $code     = ZendAuthentication\Result::SUCCESS;
            $identity = array_pop($users);

            // update last login
            $identity->setLastLogin(new \DateTime('NOW'));
            $userModel->update($identity);

            // set language
            $session = new Container('default');

            // set active language
            if (empty($this->_defaultLanguage)) {
                $this->_defaultLanguage = LangEntity::DEFAULT_LANGUAGE_CODE;
            }
            $session->offsetSet('languageCode', $this->_defaultLanguage);
        } else {

            // increment failed login log, if user exists
            $user = $userModel->findOneBy(array('email' => $this->_username));

            if($user) {
                $user->setFailedLoginCount($user->getFailedLoginCount() + 1);
                $userModel->update($user);
            }

            $code     = ZendAuthentication\Result::FAILURE;
            $identity = null;
        }

        // return an auth result object
        return new ZendAuthentication\Result($code, $identity, $messages);
    }
}