<?php

namespace Ffb\Backend\Entity;

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;

/**
 * AttributeGroupAttributeEntity
 *
 * @ORM\Entity
 * @ORM\Table(name="attribute_group_attribute")
 */
class AttributeGroupAttributeEntity extends \Ffb\Common\Entity\AbstractBaseEntity {

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(name="sort", type="integer", nullable=false, options={"unsigned":true})
     */
    protected $sort;

    /**
     * @var AttributeEntity
     * @ORM\ManyToOne(targetEntity="AttributeEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
     */
    protected $attribute;

    /**
     * @var AttributeGroupEntity
     * @ORM\ManyToOne(targetEntity="AttributeGroupEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="attribute_group_id", referencedColumnName="id")
     */
    protected $attributeGroup;

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
     * @return int
     */
    public function getSort() {
        return $this->sort;
    }

    /**
     * @param int $sort
     */
    public function setSort($sort) {
        $this->sort = $sort;
    }

    /**
     * @return AttributeEntity
     */
    function getAttribute() {

        return $this->attribute;
    }

    /**
     * @param \Ffb\Backend\Entity\AttributeEntity $attribute
     */
    function setAttribute(AttributeEntity $attribute) {

        $this->attribute = $attribute;
    }

    /**
     * @return AttributeGroupEntity
     */
    function getAttributeGroup() {

        return $this->attributeGroup;
    }

    /**
     * @param \Ffb\Backend\Entity\AttributeGroupEntity $attributeGroup
     */
    function setAttributeGroup(AttributeGroupEntity $attributeGroup) {

        $this->attributeGroup = $attributeGroup;
    }

}