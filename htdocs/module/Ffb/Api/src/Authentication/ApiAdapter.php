<?php
namespace Ffb\Api\Authentication;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Http\Request;
use Zend\ServiceManager\ServiceLocatorInterface;

//use MyApp\Repository\UserRepository;
//use \Ffb\Backend\Entity\UserEntity;

use Ffb\Backend\Entity\LangEntity;
use Ffb\Backend\Model;

use Zend\Authentication as ZendAuthentication;
use Zend\Session\Container;

class ApiAdapter implements AdapterInterface {

    //protected $_request;

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
     * ApiAdapter constructor.
     * @param ServiceLocatorInterface $sl
     * @param string $username
     * @param string $password
     * @param string $defaultLanguage
     */
    public function __construct(ServiceLocatorInterface $sl, $username, $password, $defaultLanguage = '') {
        //$this->_request    = $request;
        $this->_sl              = $sl;
        $this->_username        = $username;
        $this->_password        = $password;
        $this->_defaultLanguage = $defaultLanguage;
    }

    public function authenticate() {

        // token
        $token = time();

        // session
        $session = new Container($token);

        // get config & values
        $modConf = $this->_sl->get('Config');

        $username = $modConf['rest_api']['global']['username'];
        $password = $modConf['rest_api']['global']['password'];


        // assert that exactly one user was found that has the given credentials
        $messages = array();
        if ($this->_username == $username && $this->_password == $password) {

            $code     = ZendAuthentication\Result::SUCCESS;
            $identity = array(
                'token' => $token
            );

            // set active language
            if (empty($this->_defaultLanguage)) {
                $this->_defaultLanguage = LangEntity::DEFAULT_LANGUAGE_CODE;
            }
            $session->offsetSet('languageCode', $this->_defaultLanguage);

        } else {
            $code     = ZendAuthentication\Result::FAILURE;
            $identity = null;
        }

        // return an auth result object
        return new ZendAuthentication\Result($code, $identity, $messages);

//        $request = $this->getRequest();
//        $headers = $request->getHeaders();
//
//        // Check Authorization header presence
//        if (!$headers->has('Authorization')) {
//            return new Result(Result::FAILURE, null, array(
//                'Authorization header missing'
//            ));
//        }
//
//        // Check Authorization prefix
//        $authorization = $headers->get('Authorization')
//            ->getFieldValue();
//        if (strpos($authorization, 'PRE') !== 0) {
//            return new Result(Result::FAILURE, null, array(
//                'Missing PRE prefix'
//            ));
//        }
//
//        // Validate public key
//        $publicKey = $this->extractPublicKey($authorization);
//        $user      = $this->getUserRepository()
//            ->findByPublicKey($publicKey);
//        if (null === $user) {
//            $code = Result::FAILURE_IDENTITY_NOT_FOUND;
//            return new Result($code, null, array(
//                'User not found based on public key'
//            ));
//        }
//
//        // Validate signature
//        $signature = $this->extractSignature($authorization);
//        $hmac      = $this->getHmac($request, $user);
//        if ($signature !== $hmac) {
//            $code = Result::FAILURE_CREDENTIAL_INVALID;
//            return new Result($code, null, array(
//                'Signature does not match'
//            ));
//        }
//
//        return new Result(Result::SUCCESS, $user);
    }
}