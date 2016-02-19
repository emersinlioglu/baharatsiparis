<?php

namespace Ffb\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections;

/**
 * UserEntity
 *
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class UserEntity extends \Ffb\Common\Entity\AbstractBaseEntity {

    /**
     * @var int
     */
    const USER_STATE_LOCKED = 1;

    /**
     * @var int
     */
    const USER_STATE_NOT_LOCKED = 0;

    /**
     * id INT UNSIGNED NOT NULL
     *
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * name VARCHAR(256) NOT NULL
     *
     * @var string
     * @ORM\Column(name="name", type="string", length=256)
     */
    protected $name;

    /**
     * email VARCHAR(256) NOT NULL
     *
     * @var string
     * @ORM\Column(name="email", type="string", length=256)
     */
    protected $email;

    /**
     * password VARCHAR(256) NOT NULL
     *
     * @var string
     * @ORM\Column(name="password", type="string", length=256)
     */
    protected $password;

    /**
     * `is_locked` boolean NOT NULL DEFAULT 0
     *
     * @var boolean
     * @ORM\Column(name="is_locked", type="boolean", options={"default":0})
     */
    protected $isLocked = self::USER_STATE_NOT_LOCKED;

    /**
     * `last_login` DATETIME
     *
     * @var \DateTime
     * @ORM\Column(name="last_login", type="datetime")
     */
    protected $lastLogin;

    /**
     * `failes_login_count` INT DEFAULT 0
     *
     * @var int
     * @ORM\Column(name="failes_login_count", type="integer", options={"default":0})
     */
    protected $failedLoginCount;

    /**
     * `allow_products` boolean NOT NULL DEFAULT 0
     *
     * @var boolean
     * @ORM\Column(name="allow_products", type="boolean", options={"default":0})
     */
    protected $allowProducts;

    /**
     * `allow_attributes` boolean NOT NULL DEFAULT 0
     *
     * @var boolean
     * @ORM\Column(name="allow_attributes", type="boolean", options={"default":false})
     */
    protected $allowAttributes;

    /**
     * `allow_templates` boolean NOT NULL DEFAULT 0
     *
     * @var boolean
     * @ORM\Column(name="allow_templates", type="boolean", options={"default":false})
     */
    protected $allowTemplates;

    /**
     * `allow_admin` boolean NOT NULL DEFAULT 0
     *
     * @var boolean
     * @ORM\Column(name="allow_admin", type="boolean", options={"default":false})
     */
    protected $allowAdmin;

    /**
     * `allow_delete` boolean NOT NULL DEFAULT 0
     *
     * @var boolean
     * @ORM\Column(name="allow_delete", type="boolean", options={"default":false})
     */
    protected $allowDelete;

    /**
     * `allow_edit` boolean NOT NULL DEFAULT 0
     *
     * @var boolean
     * @ORM\Column(name="allow_edit", type="boolean", options={"default":false})
     */
    protected $allowEdit;

    /**
     * @return int $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return \Ffb\Backend\Entity\UserEntity
     */
    public function setId($id) {

        $this->id = $id;

        return $this;
    }

    /**
     * @return string $name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return \Ffb\Backend\Entity\UserEntity
     */
    public function setName($name) {

        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return \Ffb\Backend\Entity\UserEntity
     */
    public function setEmail($email) {

        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return \Ffb\Backend\Entity\UserEntity
     */
    public function setPassword($password) {

        $this->password = $password;

        return $this;
    }

    /**
     * @return bool
     */
    function getIsLocked() {
        return $this->isLocked;
    }

    /**
     * @param bool $isLocked
     */
    function setIsLocked($isLocked) {
        $this->isLocked = $isLocked;
    }

    /**
     * @return string
     */
    public function getLastLogin() {
        return $this->lastLogin;
    }

    /**
     * @param \DateTime $lastLogin
     */
    public function setLastLogin(\DateTime $lastLogin) {
        $this->lastLogin = $lastLogin;
    }

    /**
     * @return int
     */
    public function getFailedLoginCount() {
        return $this->failedLoginCount;
    }

    /**
     * @param int $failedLoginCount
     */
    public function setFailedLoginCount($failedLoginCount) {
        $this->failedLoginCount = $failedLoginCount;
    }

    /**
     * @return boolean
     */
    function getAllowProducts() {
        return $this->allowProducts;
    }

    /**
     * @param boolean $allowProducts
     */
    function setAllowProducts($allowProducts) {
        $this->allowProducts = $allowProducts;
    }

    /**
     * @return boolean
     */
    function getAllowAttributes() {
        return $this->allowAttributes;
    }


    /**
     * @param $allowAttributes
     */
    function setAllowAttributes($allowAttributes) {
        $this->allowAttributes = $allowAttributes;
    }

    /**
     * @return boolean
     */
    function getAllowTemplates() {
        return $this->allowTemplates;
    }


    /**
     * @param $allowTemplates
     */
    function setAllowTemplates($allowTemplates) {
        $this->allowTemplates = $allowTemplates;
    }

    /**
     * @return boolean
     */
    function getAllowAdmin() {
        return $this->allowAdmin;
    }


    /**
     * @param $allowAdmin
     */
    function setAllowAdmin($allowAdmin) {
        $this->allowAdmin = $allowAdmin;
    }

    /**
     * @return boolean
     */
    function getAllowDelete() {
        return $this->allowDelete;
    }


    /**
     * @param $allowDelete
     */
    function setAllowDelete($allowDelete) {
        $this->allowDelete = $allowDelete;
    }

    /**
     * @return boolean
     */
    function getAllowEdit() {
        return $this->allowEdit;
    }


    /**
     * @param $allowEdit
     */
    function setAllowEdit($allowEdit) {
        $this->allowEdit = $allowEdit;
    }

    /**
     * @return string
     */
    function getRole() {
        return 'sysadmin';
    }

}