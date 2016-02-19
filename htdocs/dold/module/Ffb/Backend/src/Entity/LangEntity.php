<?php

namespace Ffb\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Language
 *
 * @ORM\Entity
 * @ORM\Table(name="lang")
 */
class LangEntity extends \Ffb\Common\Entity\AbstractBaseEntity {

    /**
     * Default language code
     */
    const DEFAULT_LANGUAGE_CODE = 'de';

    /**
     * Default language id
     */
    const DEFAULT_LANGUAGE_ID = 1;

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
     * name VARCHAR(128) NOT NULL
     *
     * @var string
     * @ORM\Column(name="name", type="string", length=128)
     */
    protected $name;

    /**
     * `iso` VARCHAR(2) NOT NULL
     *
     * @var string
     * @ORM\Column(name="iso", type="string", length=2)
     */
    protected $iso;

    /**
     * `sort` INT NOT NULL DEFAULT 0
     *
     * @var int
     * @ORM\Column(name="sort", type="integer", options={"default":0})
     */
    protected $sort;

    /**
     * `is_active` INT NOT NULL DEFAULT 1
     *
     * @var boolean
     * @ORM\Column(name="is_active", type="boolean", options={"default":true})
     */
    protected $isActive;

    /**
     * @return the $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * @return the $name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getIso() {
        return $this->iso;
    }

    /**
     * @param string $iso
     */
    public function setIso($iso) {
        $this->iso = $iso;
        return $this;
    }

    /**
     * @return int
     */
    function getSort() {
        return $this->sort;
    }

    /**
     * @param int $sort
     * @return \Ffb\Backend\Entity\LangEntity
     */
    function setSort($sort) {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @return boolean
     */
    function getIsActive() {
        return $this->isActive;
    }

    /**
     * @param boolean $isActive
     * @return \Ffb\Backend\Entity\LangEntity
     */
    function setIsActive($isActive) {
        $this->isActive = $isActive;
        return $this;
    }
}
