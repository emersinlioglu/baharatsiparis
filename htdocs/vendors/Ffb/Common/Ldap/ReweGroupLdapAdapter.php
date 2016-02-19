<?php

namespace Ffb\Common\Ldap;

/**
 * This adapter considers the special features of the LDAP of REWE Group.
 *
 * Quirks:
 * - mail adresses are not yet considered when filtering
 *
 * @package FfbCommonLdap
 * @author marcus.gnass
 */
class ReweGroupLdapAdapter extends DefaultLdapAdapter {

    /**
     * Output debug messages.
     *
     * @var bool
     */
    const DBG = false;

    /**
     *
     * @param string $search
     * @return string
     * @see \Ffb\Common\Ldap\LdapAdapter::getOuSearchFilter()
     */
    public function getOuSearchFilter($search) {

        self::DBG && error_log("== ReweLdapAdapter::getOuSearchFilter($search)");

        return $this->_buildFilter('&', array(
            'objectclass=organizationalUnit',
            '|' => array(
                "ouShortText=*$search*",
                "ouLongText=*$search*"
            )
        ));

    }

    /**
     *
     * @return array
     * @see \Ffb\Common\Ldap\LdapAdapter::getOuAttributes()
     */
    public function getOuAttributes() {

        self::DBG && error_log("== ReweLdapAdapter::getOuAttributes()");

        return array(
            'dn',
            'ou',
            'description',
            'oushorttext',
            'oulongtext',
            'mail'
        );

    }

    /**
     * Creates a data array from a given LDAP organizational unit resultset.
     *
     * @param array $item
     * @return array
     * @see \Ffb\Common\Ldap\DefaultLdapAdapter::getOuData()
     */
    public function getOuData(array $item) {

        self::DBG && error_log("== ReweLdapAdapter::getOuData(array \$item)");

        $data = array();

        if (isset($item['dn'])) {
            $data['dn'] = $item['dn'];
        } else {
            $data['dn'] = 'n/a';
        }

        if (isset($item['ou'], $item['ou'][0])) {
            unset($item['ou']['count']);
            $data['ou'] = implode(', ', $item['ou']);
        } else {
            $data['ou'] = 'n/a';
        }

        if (isset($item['oushorttext'], $item['oushorttext'][0])) {
            $data['oushorttext'] = $item['oushorttext'][0];
        } else {
            $data['oushorttext'] = 'n/a';
        }

        if (isset($item['oulongtext'], $item['oulongtext'][0])) {
            $data['oulongtext'] = $item['oulongtext'][0];
        } else {
            $data['oulongtext'] = 'n/a';
        }

        if (isset($item['description'], $item['description'][0])) {
            $data['description'] = $item['description'][0];
        } else {
            $data['description'] = 'n/a';
        }

        if (isset($item['mail'], $item['mail'][0])) {
            $data['mail'] = $item['mail'][0];
        } else {
            $data['mail'] = 'n/a';
        }

        return $data;

    }

    /**
     *
     * @param string $search
     * @return string
     * @see \Ffb\Common\Ldap\LdapAdapter::getGroupSearchFilter()
     */
    public function getGroupSearchFilter($search) {

        self::DBG && error_log("== ReweLdapAdapter::getGroupSearchFilter($search)");

        return $this->_buildFilter('&', array(
            'objectclass=reweGroup',
            "CN=*$search*"
        ));

    }

    /**
     *
     * @return array
     * @see \Ffb\Common\Ldap\LdapAdapter::getGroupAttributes()
     */
    public function getGroupAttributes() {

        self::DBG && error_log("== ReweLdapAdapter::getGroupAttributes()");

        return array(
            'dn',
            'ou',
            'description'
        );

    }

    /**
     * Creates a data array from a given LDAP group resultset.
     *
     * @param array $item
     * @return array
     * @see \Ffb\Common\Ldap\DefaultLdapAdapter::getGroupData()
     */
    public function getGroupData(array $item) {

        self::DBG && error_log("== ReweLdapAdapter::getGroupData(array \$item)");

        $data = array();

        if (isset($item['dn'])) {
            $data['dn'] = $item['dn'];
        } else {
            $data['dn'] = 'n/a';
        }

        if (isset($item['ou'], $item['ou'][0])) {
            unset($item['ou']['count']);
            $data['ou'] = implode(', ', $item['ou']);
        } else {
            $data['ou'] = 'n/a';
        }

        if (isset($item['description'], $item['description'][0])) {
            $data['description'] = $item['description'][0];
        } else {
            $data['description'] = 'n/a';
        }

        return $data;

    }

    /**
     * Returns user filter base that will be used for each and every other user filter.
     *
     * @return string
     */
    private function _getUserFilterBase() {

        self::DBG && error_log("== DerCologneLdapAdapter::getUserFilterBase()");

        $cond = array();
        $cond[] = 'objectclass=rewePerson';
        // $cond[] = 'mail=*';
        $cond[] = 'active=true';
        $cond[] = '!(reweUserType=13)';

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

        self::DBG && error_log("== ReweLdapAdapter::getUserSearchFilter($search)");

        $cond = $this->_getUserFilterBase();
        $cond['|'] = array(
            "cn=*$search*",
            "sn=*$search*",
            "givenname=*$search*",
            "mail=*$search*"
        );

        $filter = $this->_buildFilter('&', $cond);
        self::DBG && error_log("\$filter: $filter");

        return $filter;

    }

    /**
     * Returns a filter that is suitable for looking up a user by its distinguished name.
     * filter must be set when using ldap_read,
     * therefore we use a 'non-filter' that is eq to sql 'select *'
     *
     * @return string
     * @see \Ffb\Common\Ldap\LdapAdapter::getUserByDnFilter()
     */
    public function getUserByDnFilter() {

        self::DBG && error_log("== ReweLdapAdapter::getUserByDnFilter()");

        $cond = $this->_getUserFilterBase();

        $filter = $this->_buildFilter('&', $cond);
        self::DBG && error_log("\$filter: $filter");

        return $filter;

    }

    /**
     * Returns a filter that is suitable for looking up a user by its username.
     * Which attribute is considered as username depends upon the specific LDAP.
     *
     * @param string $username
     * @return string
     * @see \Ffb\Common\Ldap\LdapAdapter::getUserByUsernameFilter()
     */
    public function getUserByUsernameFilter($username) {

        self::DBG && error_log("== ReweLdapAdapter::getUserByUsernameFilter($username)");

        $cond = $this->_getUserFilterBase();
        $cond[] = "CN=$username";

        $filter = $this->_buildFilter('&', $cond);
        self::DBG && error_log("\$filter: $filter");

        return $filter;

    }

    /**
     * Returns a filter that is suitable for looking up users that belong to a given organizational unit.
     *
     * @param string $ouDn
     * @return string
     * @see \Ffb\Common\Ldap\LdapAdapter::getUserByOuFilter()
     */
    public function getUserByOuFilter($ouDn) {

        self::DBG && error_log("== ReweLdapAdapter::getUserByOuFilter($ouDn)");

        $cond = $this->_getUserFilterBase();
        $cond[] = "organizationalunitdnstring=*$ouDn";

        $filter = $this->_buildFilter('&', $cond);
        self::DBG && error_log("\$filter: $filter");

        return $filter;

    }

    /**
     * Returns a filter that is suitable for looking up users that belong to a given group.
     *
     * @param string $groupDn
     * @return string
     * @see \Ffb\Common\Ldap\LdapAdapter::getUserByGroupFilter()
     */
    public function getUserByGroupFilter($groupDn) {

        self::DBG && error_log("== ReweLdapAdapter::getUserByGroupFilter($groupDn)");

        $cond = $this->_getUserFilterBase();
        $cond[] = "groupMembership=$groupDn";

        $filter = $this->_buildFilter('&', $cond);
        self::DBG && error_log("\$filter: $filter");

        return $filter;

    }

    /**
     * Returns a filter that is suitable for looking up users that manage a given organizational unit.
     *
     * @param string $ouDn
     * @return string
     * @see \Ffb\Common\Ldap\LdapAdapter::getUserByManagedOuFilter()
     */
    public function getUserByManagedOuFilter($ouDn) {

        self::DBG && error_log("== ReweLdapAdapter::getUserByManagedOuFilter($ouDn)");

        $cond = $this->_getUserFilterBase();
        $cond[] = "managedOU=$ouDn";

        $filter = $this->_buildFilter('&', $cond);
        self::DBG && error_log("\$filter: $filter");

        return $filter;

    }

    /**
     *
     * @return array
     * @see \Ffb\Common\Ldap\LdapAdapter::getUserAttributes()
     */
    public function getUserAttributes() {

        self::DBG && error_log("== ReweLdapAdapter::getUserAttributes()");

        return array(
            'dn', 'ou', 'cn', 'sn',
            'givenname',
            'gender',
            'mail',
            'employeenumber',
            'organizationalunitdn',
            'organizationalunitdnstring',
            'groupmembership',
            // personnelarealongtext  => costUnit
            'personnelarealongtext',
            // costcenter             => costCenter
            'costcenter',
            // companycode            => accountingArea
            'companycode',
            // organizationalunitdn   => costResponsible (indirect)
            'organizationalunitdn'
        );

    }

    /**
     * Creates a data array from a given LDAP user resultset.
     * Suitable for being used in UserModel::build().
     *
     * @param array $item
     * @return array
     * Array (
     *     [nspmpasswordpolicydn] => Array (
     *         [count] => 1
     *         [0] => cn=Standard-Password-Policy,cn=Password Policies,cn=Security
     *     )
     *     [0] => nspmpasswordpolicydn
     *     [costcenter] => Array (
     *         [count] => 1
     *         [0] => 0060041113
     *     )
     *     [1] => costcenter
     *     [givenname] => Array (
     *         [count] => 1
     *         [0] => Vorname12084
     *     )
     *     [2] => givenname
     *     [sn] => Array (
     *         [count] => 1
     *         [0] => Nachname12084
     *     )
     *     [3] => sn
     *     [ou] => Array (
     *         [count] => 1
     *         [0] => 63006956
     *     )
     *     [4] => ou
     *     [objectclass] => Array (
     *         [count] => 6
     *         [0] => Top
     *         [1] => Person
     *         [2] => organizationalPerson
     *         [3] => inetOrgPerson
     *         [4] => rewePerson
     *         [5] => ndsLoginProperties
     *     )
     *     [5] => objectclass
     *     [cn] => Array (
     *         [count] => 1
     *         [0] => A003333
     *     )
     *     [6] => cn
     *     [count] => 7
     *     [dn] => cn=A003333,ou=USR,o=REWE
     * )
     * @see \Ffb\Common\Ldap\DefaultLdapAdapter::getUserData()
     */
    public function getUserData(array $item) {

        self::DBG && error_log("== ReweLdapAdapter::getUserData(array \$item)");

        $data = array();

        // mandatory props

        if (isset($item['cn'], $item['cn'][0])) {
            $data['name'] = $item['cn'][0];
        }

        // optional props

        // ldapKey is used to login user at Eventus
        if (isset($item['cn'], $item['cn'][0])) {
            $data['ldapKey'] = $item['cn'][0];
        }

        // ldapLogin is used to login user at LDAP
        if (isset($item['dn'])) {
            $data['ldapLogin'] = $item['dn'];
        }

        if (isset($item['sn'], $item['sn'][0])) {
            $data['lastName'] = $item['sn'][0];
        }

        if (isset($item['givenname'], $item['givenname'][0])) {
            $data['firstName'] = $item['givenname'][0];
        }

        if (isset($item['gender'], $item['gender'][0])) {
            switch ($item['gender'][0]) {
                case 'male':
                    $data['gender'] = 'Herr';
                    break;
                case 'female':
                    $data['gender'] = 'Frau';
                    break;
            }
        }

        if (isset($item['mail'], $item['mail'][0])) {
            $data['email'] = strtolower($item['mail'][0]);
        }

        if (isset($item['employeenumber'], $item['employeenumber'][0])) {
            $data['personnelNumber'] = $item['employeenumber'][0];
        }

        // DEREVK-647
        if (isset($item['personnelarealongtext'], $item['personnelarealongtext'][0])) {
            $data['costUnit'] = $item['personnelarealongtext'][0];
        }

        // DERTMS-912
        if (isset($item['personnelarealongtext'], $item['personnelarealongtext'][0])) {
            $data['legalentity'] = $item['personnelarealongtext'][0];
        }

        // DEREVK-647
        if (isset($item['costcenter'], $item['costcenter'][0])) {
            $data['costCenter'] = $item['costcenter'][0];
        }

        // DEREVK-647
        if (isset($item['companycode'], $item['companycode'][0])) {
            $data['accountingArea'] = $item['companycode'][0];
        }

        // DEREVK-647
        // if (isset($item['companyCode'], $item['companyCode'][0])) {
        //     $data['costResponsible'] = $item['companyCode'][0];
        // }

        // if (isset()) {
        //     $data['sge'] = null;
        // }

        // if (isset()) {
        //     $data['area'] = null;
        // }

        // if (isset()) {
        //     $data['team'] = null;
        // }

        // if (isset()) {
        //     $data['location'] = null;
        // }

        // if (isset()) {
        //     $data['lastLogin'] = null;
        // }

        // not sure if prop "loginDisabled" or "lockedByIntruder" should be copied
        // if (isset($item['loginDisabled'])) {
        //     $data['lockUser'] = $item['loginDisabled'];
        // }

        return $data;

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

        if (!isset($item['organizationalunitdn'])) {
            return array();
        }

        $dns = $item['organizationalunitdn'];
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

            for ($i = 0; $i < count($parts); $i++) {
                list($key, $value) = explode('=', $parts[$i]);
                if ('ou' === strtolower($key)) {
                    $ldapous[] = implode(',', array_slice($parts, $i));
                }
            }
        }

        return $ldapous;

    }

}
