<?php

namespace Ffb\Backend\Entity;

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;

/**
 * AttributeValueLogEntity
 *
 * @ORM\Entity
 * @ORM\Table(name="attribute_value_log")
 */
class AttributeValueLogEntity extends \Ffb\Common\Entity\AbstractBaseEntity {

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="item", type="string", nullable=false)
     */
    protected $item;

    /**
     * @var string
     * @ORM\Column(name="old_value", type="text", nullable=true)
     */
    protected $oldValue;

    /**
     * @var string
     * @ORM\Column(name="new_value", type="text", nullable=true)
     */
    protected $newValue;

    /**
     * @var \DateTime
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    protected $date;

    /**
     * @var AttributeValueEntity
     * @ORM\ManyToOne(targetEntity="AttributeValueEntity")
     * @ORM\JoinColumn(name="attribute_value_id", referencedColumnName="id")
     */
    protected $attributeValue;

    /**
     * @var UserEntity
     * @ORM\ManyToOne(targetEntity="UserEntity")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    function getItem() {
        return $this->item;
    }

    /**
     * @param string $item
     */
    function setItem($item) {
        $this->item = $item;
    }

    /**
     * @return string
     */
    function getOldValue() {
        return $this->oldValue;
    }

    /**
     * @param string $oldValue
     */
    function setOldValue($oldValue) {
        $this->oldValue = $oldValue;
    }

    /**
     * @return string
     */
    function getNewValue() {
        return $this->newValue;
    }

    /**
     * @param string $newValue
     */
    function setNewValue($newValue) {
        $this->newValue = $newValue;
    }

    /**
     * @return \DateTime
     */
    function getDate() {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    function setDate(\DateTime $date) {
        $this->date = $date;
    }

    /**
     * @return AttributeValueEntity
     */
    function getAttributeValue() {
        return $this->attributeValue;
    }

    /**
     * @param \Ffb\Backend\Entity\AttributeValueEntity $attributeValue
     */
    function setAttributeValue(AttributeValueEntity $attributeValue) {
        $this->attributeValue = $attributeValue;
    }

    /**
     * @return UserEntity
     */
    function getUser() {
        return $this->user;
    }

    /**
     * @param \Ffb\Backend\Entity\UserEntity $user
     */
    function setUser(UserEntity $user) {
        $this->user = $user;
    }

}
