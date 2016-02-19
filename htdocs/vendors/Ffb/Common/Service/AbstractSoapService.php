<?php

namespace Ffb\Common\Service;

use Zend\Session;

/**
 * The AbstractSoapService is meant to be used as base class for SOAP client
 * services.
 *
 * @author erdal.mersinlioglu
 */
class AbstractSoapService {

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $_sl;

    /**
     * Name of the SOAP interface to use.
     *
     * An application might use several SOAP interfaces which have to be
     * distinguishable.
     *
     * @var string
     */
    protected $_interface;

    /**
     * Name of session namespace to use.
     *
     * @var string
     */
    protected $_sessionNamespace;

    /**
     * @var \Zend\Soap\Client
     */
    protected $_client;

    /**
     * @var array
     */
    protected $_cookies = array();

    /**
     * @var \Zend\Session\Container
     */
    protected $_session;

    /**
     * URL view helper
     *
     * @var \Zend\View\Helper\Url
     */
    protected $_url;

    /**
     * @var \Ffb\Eventus\Entity\UserEntity
     */
    protected $_user;

    /**
     * @var array
     */
    protected $_config;

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param string $interface
     *         name of session namespace to use
     * @param string $sessionNamespace
     *         name of session namespace to use
     */
    public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $sl, $interface, $sessionNamespace) {

        $this->_sl = $sl;
        $this->_interface = $interface;
        $this->_sessionNamespace = $sessionNamespace;

        // check SOAP extension
        if (!extension_loaded('soap')) {
            echo 'Sorry .. the SOAP slipped out of my hands.';
            return;
        }

        // for development the WSDL file should not be cached
        // is this really necessary for the client?
        $config = $this->_sl->get('Config');
        ini_set('soap.wsdl_cache_enabled', $config['service']['soap']['wsdl_cache_enabled']);

        // build new session for this service
        $this->_session = new Session\Container($this->_sessionNamespace);

        // set cookies from session if existed
        if ($this->_session->offsetExists('cookies')) {
            $this->setCookies($this->_session->cookies);
        }

        // build client
        $this->_client = $this->newClient();
    }

    /**
     * @return \Zend\Soap\Client
     */
    public function getClient() {
        return $this->_client;
    }

    /**
     * Get cookies
     *
     * @return array
     */
    public function getCookies() {
        return $this->_cookies;
    }

    /**
     * Set cookies
     *
     * @param array $_cookies
     * @return \Ffb\Common\Service\AbstractSoapService
     */
    public function setCookies(array $_cookies) {
        $this->_cookies = $_cookies;
        return $this;
    }

    /**
     * @return \Zend\Session\Container
     */
    public function getSession() {
        return $this->_session;
    }

    /**
     * @param \Zend\Session\Container $_session
     */
    public function setSession(\Zend\Session\Container $_session) {
        $this->_session = $_session;
    }

    /**
     * @return \Zend\View\Helper\Url
     */
    public function getUrl() {
        if (!$this->_url) {
            $this->_url = $this->_sl->get('ViewHelperManager')->get('url');
        }
        return $this->_url;
    }

    /**
     * @return the $_user
     */
    public function getUser() {
        return $this->_user;
    }

    /**
     * @param \Ffb\Eventus\Entity\UserEntity $_user
     */
    public function setUser($_user) {
        $this->_user = $_user;
    }

    /**
     * @return array
     */
    public function getConfig() {
        return $this->_config;
    }

    /**
     * @param array $config
     */
    public function setConfig($config) {
        $this->_config = $config;
    }

    /**
     * Return new SoapClient
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @throws \Exception
     *         if soap url to use could not be determined
     * @return \Zend\Soap\Client
     */
    public function newClient(\Zend\ServiceManager\ServiceLocatorInterface $sl = null) {

        if (!$sl) {
            $sl = $this->_sl;
        }

        // absolute path to WSDL file
        $config = $sl->get('Config');
        if (isset(
            $config['service'],
            $config['service']['soap'],
            $config['service']['soap'][$this->_interface],
            $config['service']['soap'][$this->_interface]['url']
        )) {
            $wsdl = $config['service']['soap'][$this->_interface]['url'];
        } else {
            throw new \Exception('soap url to use could not be determined');
        }

        $options = array(

            // The soap_version option should be one of either SOAP_1_1 or
            // SOAP_1_2 to select SOAP 1.1 or 1.2, respectively.
            // If omitted, 1.1 is used.
            'soap_version' => SOAP_1_2,

            // The encoding option defines internal character encoding.
            // This option does not change the encoding of SOAP requests
            // (it is always utf-8), but converts strings into it.
            'encoding' => 'UTF-8',

            // The trace option enables tracing of request so faults can be
            // backtraced. This defaults to FALSE
            // http://php.net/manual/de/soapclient.soapclient.php#111682
            'trace' => 1,

            // The exceptions option is a boolean value defining whether soap
            // errors throw exceptions of type SoapFault.
            'exceptions' => 0,

            // The connection_timeout option defines a timeout in seconds for
            // the connection to the SOAP service. This option does not define
            // a timeout for services with slow responses. To limit the time to
            // wait for calls to finish the default_socket_timeout setting is
            // available.
            'connection_timeout' => 60

        );

        // set proxy options
        if (isset($config['service']['soap'][$this->_interface]['proxy'])) {

            foreach ($config['service']['soap'][$this->_interface]['proxy'] as $key => $value) {

                if (!$value) continue;

                $options[$key] = $value;
            }
        }

        // init SOAP client in plain vanilla style
        $client = new \SoapClient($wsdl, $options);

        // init cookies if exists
        foreach ($this->getCookies() as $key => $value) {
            $client->__setCookie($key, $value);
        }

        return $client;
    }

    /**
     * Create session for SOAP.
     *
     * @param \Ffb\Eventus\Entity\UserEntity $user
     * @throws \Exception if global password check fails
     * @throws \Exception if PHPSESSID could not be determined
     * @see http://framework.zend.com/manual/2.1/en/modules/zend.session.container.html
     * @see http://framework.zend.com/apidoc/2.0/classes/Zend.Session.Container.html
     * @see http://codingexplained.com/coding/php/zend-framework/using-sessions-in-zend-framework-2
     */
    public function login(\Ffb\Eventus\Entity\UserEntity $user) {

        // DEREVK-632: Update session id, couse by server session may reach timeout
        $this->loginByEvkHash($user->getEvkHash());

        // debugging for translations on or off?
        $this->_setTranslationDebugger();
    }

    /**
     * Login by evkhash for sysadmin in tms
     *
     * @param string $evkHash
     * @throws \Exception
     *         if soap password to use could not be determined
     * @throws \Exception
     *         if connection to TMS could not be established
     * @throws \Exception
     *         if SoapFault occured
     * @throws \Exception
     *         if PHPSESSID could not be determined
     * @throws \Exception
     */
    public function loginByEvkHash($evkHash) {

        // get new client from service
        $client = $this->newClient();

        // get global password from config
        $config = $this->_sl->get('Config');
        if (isset(
            $config['service'],
            $config['service']['soap'],
            $config['service']['soap'][$this->_interface],
            $config['service']['soap'][$this->_interface]['password']
        )) {
            $soapPassword = $config['service']['soap'][$this->_interface]['password'];
        } else {
            throw new \Exception('soap password to use could not be determined');
        }

        // check global password
        $body = $client->checkGlobalPassword($soapPassword);

        if (is_object($body) && $body instanceof \SoapFault) {
            throw new \Exception($body->getMessage());
        } else if ('ok' !== $body) {
            throw new \Exception("connection to TMS could not be established: $body");
        }

        // get session ID
        $phpSessId = $client->_cookies['PHPSESSID'][0];
        if (!$phpSessId) {
            throw new \Exception('PHPSESSID could not be determined');
        }

        // store IDs in cookie
        $this->_session->cookies = array(
            'PHPSESSID' => $phpSessId,
            'EVK_HASH'  => $evkHash
        );

        $this->setCookies($this->_session->cookies);
    }

    /**
     * Performs standard checks for validity of SOAP response.
     * In case of errors an Exception will be thrown.
     *
     * The response will be interpreted as an error:
     * - if response is an instance of SoapFault
     * - if response is not an array
     * - if response array has no state
     * - if response array has a state which is not 'ok'
     *
     * @param mixed $response
     * @throws \Exception
     *         if response is or indicates an error
     */
    protected function _checkSoapResponse($response) {

        if (is_object($response) && $response instanceof \SoapFault) {
            error_log($response->getMessage());
            error_log($response->getTraceAsString());
            throw new \Exception($response->getMessage());
        }
        if (!is_array($response)) {
            throw new \Exception('response is no array');
        }
        if (!isset($response['state']) || !in_array($response['state'], array('ok', 'error'))) {
            throw new \Exception('unknown state');
        }
        if ('error' === $response['state']) {
            throw new \Exception($response['messages'][0]);
        }
    }

    /**
     * debugging for translations on or off?
     *
     */
    protected function _setTranslationDebugger() {

        // DEREVK-622 return translate-key for debugging
        $config = $this->_sl->get('Config');
        if (
                isset($_SERVER, $_SERVER['HTTP_USER_AGENT'])
             && $_SERVER['HTTP_USER_AGENT'] === $config['translator']['translation_debugger']
        ) {
             // disable translations
            $this->_session->cookies['TRANSLATION_DEBUGGER'] = true;
        } else if (isset($this->_session->cookies['TRANSLATION_DEBUGGER'])) {
             // enable translations, if was disabled
             $this->_session->cookies['TRANSLATION_DEBUGGER'] = false;
        }

        // set cookies
        $this->setCookies($this->_session->cookies);
    }
}
