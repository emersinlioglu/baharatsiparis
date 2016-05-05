<?php

namespace Ffb\Common\Ldap;

/**
 * This adapter considers the special features of the LDAP of DER Touristik Frankfurt.
 *
 * Quirks:
 * - mail adresses are not yet considered when filtering or searching
 *
 * @package FfbCommonLdap
 * @author
 */
class DerFrankfurtLdapAdapter extends DefaultLdapAdapter {

    /**
     * Output debug messages.
     *
     * @var bool
     */
    const DBG = false;

    /**
     * Returns user filter base that will be used for each and every other user filter.
     *
     * @return string
     */
    private function _getUserFilterBase() {

        self::DBG && error_log("== DerCologneLdapAdapter::getUserFilterBase()");

        $cond = array();

        $cond[] = 'objectclass=user';

        $cond[] = 'useraccountcontrol=512';

        $cond[] = '!(memberof=CN=NO-Intranet-LDAP,OU=IT-Systemgruppen-Benutzer,OU=IT,DC=DER,DC=de)';

        // mail addresses are not yet considered
        //$cond[] = 'mail=*';

        return $cond;

    }

    /**
     * Returns a filter that is suitable for searching users by a given search term.
     *
     * @param string $search
     * @return string
     * @see \Ffb\Common\Ldap\LdapAdapter::getUserSearchFilter()
     */
    public function getUserSearchFilter($search) {

        self::DBG && error_log("== DerFrankfurtLdapAdapter::getUserSearchFilter($search)");

        $cond = $this->_getUserFilterBase();
        $cond['|'] = array(
            "cn=*$search*",
            "sn=*$search*",
            // mail addresses are not yet considered
            // "mail=*$search*",
            "givenname=*$search*",
            "samaccountname=*$search*"
        );
        self::DBG && error_log("\$cond: " . var_export($cond,1));

        $filter = $this->_buildFilter('&', $cond);
        self::DBG && error_log("\$filter: $filter");

        return $filter;

    }

    /**
     *
     * Returns a filter that is suitable for looking up a user by its distinguished name.
     * filter must be set when using ldap_read,
     * therefore we use a 'non-filter' that is eq to sql 'select *'
     *
     * @return string
     * @see \Ffb\Common\Ldap\LdapAdapter::getUserByDnFilter()
     */
    public function getUserByDnFilter() {

        self::DBG && error_log("== DerFrankfurtLdapAdapter::getUserByDnFilter()");

        $cond = $this->_getUserFilterBase();

        $filter = $this->_buildFilter('&', $cond);
        self::DBG && error_log("\$filter: $filter");

        return $filter;

    }

    /**
     * Returns a filter that is suitable for looking up a user by its username.
     * The DER-LDAP considers the samaccountname as username.
     *
     * @param string $username
     * @return string
     * @see \Ffb\Common\Ldap\LdapAdapter::getUserByUsernameFilter()
     */
    public function getUserByUsernameFilter($username) {

        self::DBG && error_log("== DerFrankfurtLdapAdapter::getUserByUsernameFilter($username)");

        $cond = $this->_getUserFilterBase();
        $cond[] = "samaccountname=$username";

        $filter = $this->_buildFilter('&', $cond);
        self::DBG && error_log("\$filter: $filter");

        return $filter;

    }

    /**
     * Accessible attributes:
     * <ul>
     * <li>cn                       (TEST, LIVE)
     * <li>instanceType             (    , LIVE)
     * <li>nTSecurityDescriptor     (    , LIVE)
     * <li>objectCategory           (TEST, LIVE)
     * <li>objectClass              (TEST, LIVE)
     * <li>c                        (    , LIVE)
     * <li>accountExpires           (TEST, LIVE)
     * <li>badPasswordTime          (TEST,     )
     * <li>badPwdCount              (TEST,     )
     * <li>codePage                 (TEST, LIVE)
     * <li>counryCode               (TEST, LIVE)
     * <li>description
     * <li>displayName              (TEST, LIVE)
     * <li>distinguishedName        (TEST, LIVE)
     * <li>dSCorePropagationData    (TEST, LIVE)
     * <li>extensionAttribute
     * <li>givenName                (    , LIVE)
     * <li>lastLogoff               (TEST,     )
     * <li>lastLogon                (TEST,     )
     * <li>lastLogonTimestamp       (TEST, LIVE)
     * <li>logonCount               (TEST,     )
     * <li>name                     (TEST, LIVE)
     * <li>objectGUID               (TEST, LIVE)
     * <li>objectSid                (TEST, LIVE)
     * <li>primaryGroupID           (TEST, LIVE)
     * <li>pwdLastSet               (TEST, LIVE)
     * <li>sAMAccountName           (TEST, LIVE)
     * <li>sAMAccountType           (TEST, LIVE)
     * <li>sn                       (      LIVE)
     * <li>userAccountControl       (TEST, LIVE)
     * <li>userPrincipalName        (TEST, LIVE)
     * <li>uSNChanged               (TEST, LIVE)
     * <li>uSNCreated               (TEST, LIVE)
     * <li>whenChanged              (TEST, LIVE)
     * <li>whenCreated              (TEST, LIVE)
     * </ul>
     *
     * @return array
     * @see \Ffb\Common\Ldap\LdapAdapter::getUserAttributes()
     */
    public function getUserAttributes()  {

        self::DBG && error_log("== DerFrankfurtLdapAdapter::getUserAttributes()");

        $attributes = array(
            'distinguishedName',
            'sAMAccountName',
            'sn',
            'givenname',
            'mail',
            // 'gender',
            // 'employeenumber',
            // 'ou'
        );

        self::DBG && error_log(print_r($attributes,1));

        return $attributes;

    }

    /**
     * Converts an LDAP data array to an user data array which is suitable for
     * being used in UserModel::build().
     *
     * The LDAP data array is in fact an LDAP user search resultset with data
     * as specified by getUserAttributes().
     *
     * @param array $ldapData
     *         an LDAP user search resultset
     * @return array
     *         a user data array
     * @see \Ffb\Common\Ldap\DefaultLdapAdapter::getUserData()
     */
    public function getUserData(array $ldapData) {

        self::DBG && error_log("== DerFrankfurtLdapAdapter::getUserData(array \$item)");

        self::DBG && error_log(print_r($ldapData,1));

        $userData = array();

        // mandatory props

        // name is used to login user at Eventus
        if (isset($ldapData['samaccountname'], $ldapData['samaccountname'][0])) {
            $userData['name'] = $ldapData['samaccountname'][0];
        }

        // optional props

        if (isset($ldapData['samaccountname'], $ldapData['samaccountname'][0])) {
            $userData['ldapKey'] = $ldapData['samaccountname'][0];
        }

        // ldapLogin (DN) is used to login user at LDAP
        if (isset($ldapData['distinguishedname'], $ldapData['distinguishedname'][0])) {
            $userData['ldapLogin'] = $ldapData['distinguishedname'][0];
        }

        if (isset($ldapData['sn'], $ldapData['sn'][0])) {
            $userData['lastName'] = $ldapData['sn'][0];
        }

        if (isset($ldapData['givenname'], $ldapData['givenname'][0])) {
            $userData['firstName'] = $ldapData['givenname'][0];
        }

        if (isset($ldapData['mail'], $ldapData['mail'][0])) {
            $userData['email'] = strtolower($ldapData['mail'][0]);
        }

        self::DBG && error_log(print_r($userData,1));

        return $userData;

    }

    /**
     * Creates an array of DNs of organizational units a given user belongs to.
     *
     * @param array $item
     *         native (LDAP-dependant) user data
     * @return array
     * @see \Ffb\Common\Ldap\LdapAdapter::getUserOrganizationalUnits()
     */
    public function getUserOrganizationalUnits(array $item) {

        if (!isset($item['distinguishedname'])) {
            return array();
        }

        $dns = $item['distinguishedname'];
        self::DBG && error_log('$dns: ' . print_r($dns,1));
        unset($dns['count']);

        $ldapous = array();
        foreach ($dns as $dn) {

            // split by commata
            // $parts = explode(',', $dn);
            // split by (unescaped!) commata
            // $parts = preg_split("/[^\\\\][,]/", $dn);
            // split the clever way!
            $parts = ldap_explode_dn($dn, 0);
            unset($parts['count']);
            self::DBG && error_log('$parts: ' . print_r($parts,1));

            // start from 1 as the first part is the users CN!
            for ($i = 1; $i < count($parts); $i++) {
                self::DBG && error_log('$i: ' . print_r($i,1));
                list($key, $value) = explode('=', $parts[$i]);
                self::DBG && error_log('$parts[$i]: ' . print_r($parts[$i],1));
                if ('ou' === strtolower($key)) {
                    $ldapous[] = implode(',', array_slice($parts, $i));
                }
            }
        }

        self::DBG && error_log('$ldapous: ' . print_r($ldapous,1));

        return $ldapous;

    }

}
