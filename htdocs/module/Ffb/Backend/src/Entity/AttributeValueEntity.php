<?php

namespace Ffb\Backend\Entity;

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;

/**
 * AttributeValueEntity
 * This class extends the common FFB AbstractUserOwnedEntity class!
 *
 * @ORM\Entity
 * @ORM\Table(name="attribute_value")
 */
class AttributeValueEntity extends \Ffb\Common\Entity\AbstractBaseEntity {

    /**
     * Image reference for upload
     *
     * @var string
     */
    const FILE_REF = 'attributevalue';

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="value", type="string", length=255, nullable=true, options={"default":null})
     */
    protected $value;

    /**
     * @var string
     * @ORM\Column(name="value_min", type="string", length=255, nullable=true, options={"default":null})
     */
    protected $valueMin;

    /**
     * @var string
     * @ORM\Column(name="value_max", type="string", length=255, nullable=true, options={"default":null})
     */
    protected $valueMax;

    /**
     * @var string
     * @ORM\Column(name="description", type="string", length=255, nullable=true, options={"default":null})
     */
    protected $description;

    /**
     * @var boolean
     * @ORM\Column(name="is_inherited", type="boolean", nullable=false, options={"default":false})
     */
    protected $isInherited = false;

    /**
     * @var ProductLangEntity
     * @ORM\ManyToOne(targetEntity="ProductLangEntity")
     * @ORM\JoinColumn(name="product_lang_id", referencedColumnName="id")
     */
    protected $productLang;

    /**
     * @var AttributeLangEntity
     * @ORM\ManyToOne(targetEntity="AttributeLangEntity")
     * @ORM\JoinColumn(name="attribute_lang_id", referencedColumnName="id")
     */
    protected $attributeLang;

    /**
     * @var AttributeGroupEntity
     * @ORM\ManyToOne(targetEntity="AttributeGroupEntity")
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
     * @return string
     */
    function getValue() {
        return $this->value;
    }

    /**
     * @param string $value
     */
    function setValue($value) {
        $this->value = $value;
    }

    /**
     * @return string
     */
    function getValueMin() {
        return $this->valueMin;
    }

    /**
     * @param string $valueMin
     */
    function setValueMin($valueMin) {
        $this->valueMin = $valueMin;
    }

    /**
     * @return string
     */
    function getValueMax() {
        return $this->valueMax;
    }

    /**
     * @param string $valueMax
     */
    function setValueMax($valueMax) {
        $this->valueMax = $valueMax;
    }

    /**
     * @return string
     */
    function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     */
    function setDescription($description) {
        $this->description = $description;
    }

    /**
     * @return boolean
     */
    function getIsInherited() {
        return $this->isInherited;
    }

    /**
     * @param boolean $isInherited
     */
    function setIsInherited($isInherited) {
        $this->isInherited = $isInherited;
    }

    /**
     * @return ProductLangEntity
     */
    function getProductLang() {
        return $this->productLang;
    }

    /**
     * @param \Ffb\Backend\Entity\ProductLangEntity $productLang
     */
    function setProductLang(ProductLangEntity $productLang = null) {
        $this->productLang = $productLang;
    }

    /**
     * @return AttributeLangEntity
     */
    function getAttributeLang() {
        return $this->attributeLang;
    }

    /**
     * @param \Ffb\Backend\Entity\AttributeLangEntity $attributeLang
     */
    function setAttributeLang(AttributeLangEntity $attributeLang = null) {
        $this->attributeLang = $attributeLang;
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
