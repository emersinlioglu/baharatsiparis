<?php

namespace Ffb\Common\Ldap;

/**
 * Simple class to access an LDAP.
 *
 * @author claus.schunk
 * @author marcus.gnass
 */
class LdapAuth {

    /**
     * Output debug messages.
     *
     * @var bool
     */
    const DBG = false;

    /**
     * Options to connect to LDAP.
     *
     * <dl>
     * <dt>host
     * <dd>name or IP of LDAP host
     * <dt>port
     * <dd>port of LDAP service
     * <dt>username
     * <dd>relative distinguished name to be used for binding
     * <dt>password
     * <dd>password to be used for binding
     * <dt>options
     * <dd>options to be set via ldap_set_option()
     * </dl>
     *
     * @var array
     */
    private $_opt = array();

    /**
     * LDAP connection resource.
     *
     * @var resource
     */
    private $_handler;

    /**
     * If an LDAP search returned only a partial result.
     * Due to warning "Partial search results returned: Sizelimit exceeded".
     *
     * @var bool
     */
    private $_partial = false;

    /**
     * Connects and binds to LDAP.
     *
     * @param array $opt
     *         <dl>
     *         <dt>host
     *         <dd>name or IP of LDAP host
     *         <dt>port
     *         <dd>port of LDAP service
     *         <dt>username
     *         <dd>relative distinguished name to be used for binding
     *         <dt>password
     *         <dd>password to be used for binding
     *         <dt>options
     *         <dd>options to be set via ldap_set_option()
     *         </dl>
     * @throws \Exception
     *         if host is missing
     * @throws \Exception
     *         if port is missing
     * @throws \Exception
     *         if username is missing
     * @throws \Exception
     *         if password is missing
     * @throws \Exception
     *         if bind to LDAP failed
     */
    public function open(array $opt) {

        if (!isset($opt['host'])) {
            throw new \Exception('host is missing');
        }

        if (!isset($opt['port'])) {
            throw new \Exception('port is missing');
        }

        if (!isset($opt['username'])) {
            throw new \Exception('username is missing');
        }

        if (!isset($opt['password'])) {
            throw new \Exception('password is missing');
        }

        if (isset($opt['options']) && is_array($opt['options'])) {
            foreach ($opt['options'] as $key => $value) {
                ldap_set_option($this->_handler, $key, $value);
            }
        }

        // connect to LDAP
        $this->_handler = ldap_connect($opt['host'], (int) $opt['port']);

        // set LDAP options (if defined)
        if (isset($opt['options']) && is_array($opt['options'])) {
            foreach ($opt['options'] as $key => $value) {
                ldap_set_option($this->_handler, $key, $value);
            }
        }

        // bind to LDAP
        $succ = @ldap_bind($this->_handler, $opt['username'], $opt['password']);
        if (!$succ) {
            $this->close();
            throw new \Exception('bind to LDAP failed');
        }

    }

    /**
     * Performs a read in the connected LDAP
     * and returns <strong>all entries at once</strong>.
     *
     * @param string $baseDn
     * @param string $filter
     * @param array $attributes [optional]
     * @param int $attrsonly [optional]
     * @param int $sizelimit [optional]
     * @param int $timelimit [optional]
     * @param int $deref [optional]
     * @return array|false
     *         If no LDAP is connected or an error occurs when searching
     *         false is returned.
     */
    public function read($baseDn, $filter, array $attributes = array(), $attrsonly = 0, $sizelimit = 0, $timelimit = 0, $deref = LDAP_DEREF_NEVER) {

        self::DBG && error_log("== LdapAuth::read($baseDn, $filter, \$attributes, $attrsonly, $sizelimit, $timelimit, $deref)");

        if (!$this->_handler) {
            return false;
        }

        // perform search
        // $result = ldap_read($this->_handler, $baseDn, $filter, $attributes, $attrsonly, $sizelimit, $timelimit, $deref);
        $result = ldap_read($this->_handler, $baseDn, $filter, $attributes);
        self::DBG && error_log("ldap_read(\$this->_handler, '$baseDn', '$filter')");

        // get entries
        if ($result) {
            $result = ldap_get_entries($this->_handler, $result);
        } else {
            $this->errno = ldap_errno($this->_handler);
            $this->error = ldap_error($this->_handler);
            $this->err2str = ldap_err2str($this->errno);
        }

        return $result;
    }

    /**
     * Performs a list in the connected LDAP
     * and returns <strong>all entries at once</strong>.
     *
     * @link http://php.net/ldap_list
     * @param string $baseDn
     * @param string $filter
     * @param array $attributes [optional]
     * @param int $attrsonly [optional]
     * @param int $sizelimit [optional]
     * @param int $timelimit [optional]
     * @param int $deref [optional]
     * @return array|false
     *         If no LDAP is connected or an error occurs when searching
     *         false is returned.
     */
    public function ldap_list($baseDn, $filter, array $attributes = array(), $attrsonly = 0, $sizelimit = 0, $timelimit = 0, $deref = LDAP_DEREF_NEVER) {

        self::DBG && error_log("== LdapAuth::ldap_list($baseDn, $filter, \$attributes, $attrsonly, $sizelimit, $timelimit, $deref)");

        if (!$this->_handler) {
            return false;
        }

        // set the error handler
        $this->_setPartialSearchErrorHandler();

        // perform search
        //$result = ldap_list($this->_handler, $baseDn, $filter, $attributes, $attrsonly, $sizelimit, $timelimit, $deref);
        $result = ldap_list($this->_handler, $baseDn, $filter, $attributes, $attrsonly, $sizelimit);
        self::DBG && error_log("ldap_list(\$this->_handler, '$baseDn', '$filter')");

        // restore the error handler
        $this->_restoreErrorHandler();

        // get entries
        if ($result) {
            $result = ldap_get_entries($this->_handler, $result);
        } else {
            $this->errno = ldap_errno($this->_handler);
            $this->error = ldap_error($this->_handler);
            $this->err2str = ldap_err2str($this->errno);
        }

        return $result;
    }

    /**
     * Performs a search in the connected LDAP
     * and returns <strong>all entries at once</strong>.
     *
     * @param string $baseDn
     * @param string $filter
     * @param array $attributes [optional]
     * @param int $attrsonly [optional]
     * @param int $sizelimit [optional]
     * @param int $timelimit [optional]
     * @param int $deref [optional]
     * @return array|false
     *         If no LDAP is connected or an error occurs when searching
     *         false is returned.
     */
    public function search($baseDn, $filter, array $attributes = array(), $attrsonly = 0, $sizelimit = 0, $timelimit = 0, $deref = LDAP_DEREF_NEVER) {

        self::DBG && error_log("== LdapAuth::search($baseDn, $filter, \$attributes, $attrsonly, $sizelimit, $timelimit, $deref)");

        if (!$this->_handler) {
            return false;
        }

        // set the error handler
        $this->_setPartialSearchErrorHandler();

        // LDAP_DEREF_NEVER - (default) aliases are never dereferenced.
        // LDAP_DEREF_SEARCHING - aliases should be dereferenced during the search but not when locating the base object of the search.
        // LDAP_DEREF_FINDING - aliases should be dereferenced when locating the base object but not during the search.
        // LDAP_DEREF_ALWAYS - aliases should be dereferenced always.

        // perform search
        //$result = ldap_search($this->_handler, $baseDn, $filter, $attributes, $attrsonly, $sizelimit, $timelimit, $deref);
        self::DBG && error_log("CALL ldap_search()");
        $result = ldap_search($this->_handler, $baseDn, $filter, $attributes, $attrsonly, $sizelimit, 0, LDAP_DEREF_NEVER);
        self::DBG && error_log("DONE ldap_search()");

        // restore the error handler
        $this->_restoreErrorHandler();

        // get entries
        if ($result) {
            $result = ldap_get_entries($this->_handler, $result);
        } else {
            $this->errno = ldap_errno($this->_handler);
            $this->error = ldap_error($this->_handler);
            $this->err2str = ldap_err2str($this->errno);
        }

        return $result;
    }

    /**
     * Closes a connection to an LDAP.
     */
    public function close() {

        if (!$this->_handler) {
            return;
        }

        @ldap_unbind($this->_handler);

    }

    /**
     * Error handler to catch warnings.
     * E.g. "Partial search results returned: Sizelimit exceeded"
     *
     * @link http://stackoverflow.com/questions/1241728/can-i-try-catch-a-warning
     */
    private function _setPartialSearchErrorHandler() {
        // reset prop
        $this->_partial = false;
        // set error handler
        set_error_handler(array($this, 'partialSearchErrorHandler'));
    }

    /**
     * Restore previous error handler.
     *
     * @link http://stackoverflow.com/questions/1241728/can-i-try-catch-a-warning
     */
    private function _restoreErrorHandler() {
        // restore error handler
        restore_error_handler();
    }

    /**
     * The error handler itself.
     *
     * @link http://stackoverflow.com/questions/1241728/can-i-try-catch-a-warning
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param array $errcontext
     */
    public function partialSearchErrorHandler($errno, $errstr, $errfile, $errline, array $errcontext) {
        self::DBG && error_log('$errno: ' . $errno);
        self::DBG && error_log('$errstr: ' . $errstr);
        self::DBG && error_log('$errfile: ' . $errfile);
        self::DBG && error_log('$errline: ' . $errline);
        self::DBG && error_log('$errcontext: ' . print_r($errcontext,1));
        if (false !== strpos($errstr, 'Partial search results returned: Sizelimit exceeded')) {
            $this->_partial = true;
        }
    }

}
