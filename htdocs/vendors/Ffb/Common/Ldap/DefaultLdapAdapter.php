<?php

namespace Ffb\Common\Ldap;

/**
 *
 * @package FfbCommonLdap
 * @author marcus.gnass
 */
class DefaultLdapAdapter extends LdapAdapter {

    /**
     *
     * @param string $search
     * @return string
     * @see \Ffb\Common\Ldap\LdapAdapter::getOuSearchFilter()
     */
    public function getOuSearchFilter($search) {
        return '';
    }

    /**
     *
     * @return array
     * @see \Ffb\Common\Ldap\LdapAdapter::getOuAttributes()
     */
    public function getOuAttributes() {
        return array();
    }

    /**
     * Creates a data array from a given LDAP organizational unit resultset.
     *
     * @param array $item
     * @return array
     * @see \Ffb\Common\Ldap\LdapAdapter::getOuData()
     */
    public function getOuData(array $item) {
        return array();
    }

    /**
     *
     * @param string $search
     * @return string
     * @see \Ffb\Common\Ldap\LdapAdapter::getGroupSearchFilter()
     */
    public function getGroupSearchFilter($search) {
        return '';
    }

    /**
     *
     * @return array
     * @see \Ffb\Common\Ldap\LdapAdapter::getGroupAttributes()
     */
    public function getGroupAttributes() {
        return array();
    }

    /**
     * Creates a data array from a given LDAP group resultset.
     *
     * @param array $item
     * @return array
     * @see \Ffb\Common\Ldap\LdapAdapter::getGroupData()
     */
    public function getGroupData(array $item) {
        return array();
    }

    /**
     * Returns a filter that is suitable for searching users by a given search term.
     *
     * @param string $search
     * @return string
     * @see \Ffb\Common\Ldap\LdapAdapter::getUserSearchFilter()
     */
    public function getUserSearchFilter($search) {
        return '';
    }

    /**
     * Returns a filter that is suitable for looking up a user by its distinguished name.
     *
     * @return string
     * @see \Ffb\Common\Ldap\LdapAdapter::getUserByDnFilter()
     */
    public function getUserByDnFilter() {
        return '';
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
        return '';
    }

    /**
     * Returns a filter that is suitable for looking up users that belong to a given organizational unit.
     *
     * @param string $ouDn
     * @return string
     * @see \Ffb\Common\Ldap\LdapAdapter::getUserByOuFilter()
     */
    public function getUserByOuFilter($ouDn) {
        return '';
    }

    /**
     * Returns a filter that is suitable for looking up users that belong to a given group.
     *
     * @param string $groupDn
     * @return string
     * @see \Ffb\Common\Ldap\LdapAdapter::getUserByGroupFilter()
     */
    public function getUserByGroupFilter($groupDn) {
        return '';
    }

    /**
     * Returns a filter that is suitable for looking up users that manage a given organizational unit.
     *
     * @param string $ouDn
     * @return string
     * @see \Ffb\Common\Ldap\LdapAdapter::getUserByManagedOuFilter()
     */
    public function getUserByManagedOuFilter($ouDn) {
        return '';
    }

    /**
     *
     * @return array
     * @see \Ffb\Common\Ldap\LdapAdapter::getUserAttributes()
     */
    public function getUserAttributes() {
        return array();
    }

    /**
     * Creates a data array from a given LDAP user resultset.
     * Suitable for being used in UserModel::build().
     *
     * @param array $item
     *         native (LDAP-dependant) user data
     * @return array
     *         generic (LDAP-independant) user data
     * @see \Ffb\Common\Ldap\LdapAdapter::getUserData()
     */
    public function getUserData(array $item) {
        return array();
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
        return array();
    }

}
