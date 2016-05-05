<?php

namespace Ffb\Backend\Model;

use Ffb\Backend\Entity;

use Zend\ServiceManager;

/**
 * @author
 * @author erdal.mersinlioglu
 */
class UserModel extends AbstractBaseModel {

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param \Zend\Log\Logger $logger
     */
    public function __construct(ServiceManager\ServiceLocatorInterface $sl, \Zend\Log\Logger $logger = null) {
        $this->_entityClass = 'Ffb\Backend\Entity\UserEntity';
        parent::__construct($sl, $logger);
    }

    /**
     * Return Sysadmin (i.e. user w/ Name sysadmin).
     *
     * @return Entity\UserEntityy
     */
    public function findSysadmin() {
        $sysadmin = $this->findOneBy(array('name' => Entity\UserEntity::USER_SYSADMIN .'a'));
        if (!$sysadmin) {
            $sysadmin = new Entity\UserEntity();
            $sysadmin->setName(Entity\UserEntity::USER_SYSADMIN.'a');
            $sysadmin->setEmail(Entity\UserEntity::USER_SYSADMIN.'a@4fb.de');
            $sysadmin->setPassword('sysadmin');
            $sysadmin->setAllowProducts(true);
            $sysadmin->setAllowAdmin(true);
            $sysadmin->setAllowAttributes(true);
            $sysadmin->setAllowDelete(true);
            $sysadmin->setAllowEdit(true);
            $sysadmin->setAllowTemplates(true);
            $this->insert($sysadmin);
        }
        return $sysadmin;
    }

    /**
     * Find user by credentials.
     *
     * The users email address is used as user name.
     *
     * The password will be hashed before it is checked against the hashed
     * password in the database. This hashing uses a salt that can be defined
     * in the application config as acl/passwordSalt.
     *
     * When reading users only active users (state=0) and those with no
     * expiration date or an expiration date in the future are considered.
     *
     * @param string $username
     * @param string $password
     * @return array
     */
    public function findByCredentials($username, $password) {

        $params = array(
            'email' => $username,
            //'password' => $this->_hashPassword($password),
            'password' => $password,
            'isLocked' => !Entity\UserEntity::USER_STATE_LOCKED
        );

        $qb = $this->getRepository()->createQueryBuilder('user');

        // check expired
        /*$notExpired = $qb->expr()->orx();
        $notExpired->add('user.expired >= :expired');
        $notExpired->add('user.expired IS NULL');*/

        $qb->where('user.email = :email')
           ->andWhere('user.password = :password')
           ->andWhere('user.isLocked = :isLocked')
           /*->andWhere($notExpired)*/
           ->setParameters($params);

        return $qb->getQuery()->getResult();
    }

    /**
     * Encrypt password with salt from config
     *
     * @param string $password
     */
    private function _hashPassword($password) {

        $conf = $this->_sl->get('Application\Config');
        $salt = $conf['acl']['passwordSalt'];

        $password = $salt . $password . $salt;
        $password = sha1($password);

        return $password;
    }

    /**
     * Search users for users list
     *
     * @param string $filter
     * @param string $search
     */
    public function findForList($filter = null, $search = null) {

        $qb = $this->getRepository()->createQueryBuilder('user');

        // show active users only
        $qb->where('user.state = :state')
           ->setParameter('state', Entity\UserEntity::USER_STATE_ACTIVE);

        // set filter by role
        if (!is_null($filter)) {

            if (!is_array($filter)) {
                $filter = array($filter);
            }

            $roleFilter = $qb->expr()->orx();
            foreach ($filter as $i => $role) {
                $roleFilter->add('user.role = :role' . $i);
                $qb->setParameter('role' . $i, $role);
            }
            $qb->andWhere($roleFilter);
        }

        // set search by name, firstname, lastname
        if (!is_null($search)) {

            $nameSearch = $qb->expr()->orx();
            $nameSearch->add('user.name LIKE :name');
            $nameSearch->add('user.firstName LIKE :name');
            $nameSearch->add('user.lastName LIKE :name');

            $qb->andWhere($nameSearch)
               ->setParameter('name', '%' . $search . '%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     *
     * @param Entity\UserEntity $user
     * @return boolean
     */
    public function isEmailUnique($user) {

        $qb = $this->getRepository()->createQueryBuilder('user');

        $qb->where('user.email = :email')
            ->setParameter('email', $user->getEmail());

        // TODO check, do we allow new email if same email in inactive/deleted state?

        $result = $qb->getQuery()->getResult();
        if (
            count($result) === 0 ||
            (count($result) === 1 && $result[0]->getId() === $user->getId())
        ) {
            return true;
        }

        return false;
    }
}