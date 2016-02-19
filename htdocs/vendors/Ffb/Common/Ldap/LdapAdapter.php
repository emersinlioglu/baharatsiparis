<?php

namespace Ffb\Common\Ldap;

/**
 *
 * @package FfbCommonLdap
 * @author marcus.gnass
 */
abstract class LdapAdapter {

    /**
     *
     * @param string $search
     * @return string
     */
    abstract public function getOuSearchFilter($search);

    /**
     *
     * @return array
     */
    abstract public function getOuAttributes();

    /**
     * Creates a data array from a given LDAP organizational unit resultset.
     *
     * @param array $item
     * @return array
     */
    abstract public function getOuData(array $item);

    /**
     *
     * @param string $search
     * @return string
     */
    abstract public function getGroupSearchFilter($search);

    /**
     *
     * @return array
     */
    abstract public function getGroupAttributes();

    /**
     * Creates a data array from a given LDAP group resultset.
     *
     * @param array $item
     * @return array
     */
    abstract public function getGroupData(array $item);

    /**
     * Returns a filter that is suitable for searching users by a given search term.
     *
     * @param string $search
     * @return string
     */
    abstract public function getUserSearchFilter($search);

    /**
     * Returns a filter that is suitable for looking up a user by its distinguished name.
     *
     * @return string
     */
    abstract public function getUserByDnFilter();

    /**
     * Returns a filter that is suitable for looking up a user by its username.
     * Which attribute is considered as username depends upon the specific LDAP.
     *
     * @param string $username
     * @return string
     */
    abstract public function getUserByUsernameFilter($username);

    /**
     * Returns a filter that is suitable for looking up users that belong to a given organizational unit.
     *
     * @param string $ouDn
     * @return string
     */
    abstract public function getUserByOuFilter($ouDn);

    /**
     * Returns a filter that is suitable for looking up users that belong to a given group.
     *
     * @param string $groupDn
     * @return string
     */
    abstract public function getUserByGroupFilter($groupDn);

    /**
     * Returns a filter that is suitable for looking up users that manage a given organizational unit.
     *
     * @param string $ouDn
     * @return string
     */
    abstract public function getUserByManagedOuFilter($ouDn);

    /**
     *
     * @return array
     */
    abstract public function getUserAttributes();

    /**
     * Creates a data array from a given LDAP user resultset.
     * Suitable for being used in UserModel::build().
     *
     * @param array $item
     *         native (LDAP-dependant) user data
     * @return array
     *         generic (LDAP-independant) user data
     */
    abstract public function getUserData(array $item);

    /**
     * Creates an array of DNs of organizational units a given user belongs to.
     *
     * @param array $item
     *         native (LDAP-dependant) user data
     * @return array
     */
    abstract public function getUserOrganizationalUnits(array $item);

    /**
     * Helper method to build LDAP filter from structured array
     * which is much more readable.
     *
     * @param string $operator
     * @param array $criteria
     * @return string
     */
    protected function _buildFilter($operator, array $criteria) {
        $filter = '';
        foreach ($criteria as $key => $crit) {
            $filter .= is_array($crit)
                ? $this->_buildFilter($key, $crit)
                : "($crit)";
        }
        return "($operator$filter)";
    }

}
