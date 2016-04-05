<?php

namespace Ffb\Backend\Entity;

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;

/**
 * AttributeEntity
 * This class extends the common FFB AbstractUserOwnedEntity class!
 *
 * @ORM\Entity
 * @ORM\Table(name="attribute")
 */
class AttributeEntity extends \Ffb\Backend\Entity\AbstractTranslatableEntity {

    /**
     * @var string
     */
    protected $_translationEntity = 'Ffb\Backend\Entity\AttributeLangEntity';

    /**
     * @var int
     */
    const TYPE_VARCHAR = 1;

    /**
     * @var int
     */
    const TYPE_TEXT = 2;

    /**
     * @var int
     */
    const TYPE_INT = 3;

    /**
     * @var int
     */
    const TYPE_FLOAT = 4;

    /**
     * @var int
     */
    const TYPE_BOOL = 5;

    /**
     * @var int
     */
    const TYPE_RANGE_INT = 6;

    /**
     * @var int
     */
    const TYPE_RANGE_FLOAT = 7;

    /**
     * @var int
     */
    const TYPE_IMAGE = 8;

    /**
     * @var int
     */
    const TYPE_DOCUMENT = 9;

    /**
     * @var int
     */
    const TYPE_SELECT = 10;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(name="type", type="integer", nullable=false, options={"unsigned":true})
     */
    protected $type;

    /**
     * @var int
     * @ORM\Column(name="length", type="integer", nullable=false, options={"default":255, "unsigned":true})
     */
    protected $length;

    /**
     * @var boolean
     * @ORM\Column(name="is_uppercase", type="boolean", nullable=true)
     */
    protected $isUppercase = null;

    /**
     * @var boolean
     * @ORM\Column(name="is_active", type="boolean", nullable=false, options={"default": true})
     */
    protected $isActive = true;

    /**
     * @var boolean
     * @ORM\Column(name="is_multi_select", type="boolean", nullable=false, options={"default": false})
     */
    protected $isMultiSelect = false;

    /**
     * @var string
     * @ORM\Column(name="option_values", type="string", length=255, nullable=true, options={"default": null})
     */
    protected $optionValues;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="AttributeLangEntity", mappedBy="translationTarget", cascade={"persist", "remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $translations;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="AttributeGroupAttributeEntity", mappedBy="attribute", cascade={"persist", "remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $attributeGroupAttributes;

    public function __construct() {
        parent::__construct();

        $this->attributeGroupAttributes = new Collections\ArrayCollection();
    }

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
    public function getType() {
        return $this->type;
    }

    /**
     * @param int $type
     * @return \Ffb\Backend\Entity\AttributeEntity
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    function getLength() {
        return $this->length;
    }

    /**
     * @param int $length
     */
    function setLength($length) {
        $this->length = $length;
    }

    /**
     * @return boolean
     */
    function getIsUppercase() {
        return $this->isUppercase;
    }

    /**
     * @param boolean $isUppercase
     */
    function setIsUppercase($isUppercase) {
        $this->isUppercase = $isUppercase;
    }

    /**
     * @return boolean
     */
    function getIsActive() {
        return $this->isActive;
    }

    /**
     * @param boolean $isActive
     * @return $this
     */
    function setIsActive($isActive) {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsMultiSelect() {
        return $this->isMultiSelect;
    }

    /**
     * @param boolean $isMultiSelect
     * @return AttributeEntity
     */
    public function setIsMultiSelect($isMultiSelect) {
        $this->isMultiSelect = $isMultiSelect;
        return $this;
    }

    /**
     * @return string
     */
    public function getOptionValues() {
        return $this->optionValues;
    }

    /**
     * @param string $optionValues
     * @return AttributeEntity
     */
    public function setOptionValues($optionValues) {
        $this->optionValues = $optionValues;
        return $this;
    }

    /**
     * @return Collections\ArrayCollection
     */
    function getAttributeGroupAttributes() {
        return $this->attributeGroupAttributes;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $attributeGroupAttributes
     */
    function setAttributeGroupAttributes(\Doctrine\Common\Collections\ArrayCollection $attributeGroupAttributes) {
        $this->attributeGroupAttributes = $attributeGroupAttributes;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $attributeGroupAttributes
     */
    public function addAttributeGroupAttributes(\Doctrine\Common\Collections\Collection $attributeGroupAttributes) {
        foreach ($attributeGroupAttributes as $attributeGroupAttribute) {
            $attributeGroupAttribute->setAttribute($this);
            $this->attributeGroupAttributes->add($attributeGroupAttribute);
        }
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $attributeGroupAttributes
     */
    public function removeAttributeGroupAttributes(\Doctrine\Common\Collections\Collection $attributeGroupAttributes) {
        foreach ($attributeGroupAttributes as $attributeGroupAttribute) {
            $attributeGroupAttribute->setAttribute(null);
            $this->attributeGroupAttributes->removeElement($attributeGroupAttribute);
        }
    }

}
