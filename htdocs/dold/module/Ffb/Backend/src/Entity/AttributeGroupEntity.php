<?php

namespace Ffb\Backend\Entity;

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;

/**
 * AttributeGroupEntity
 *
 * @ORM\Entity
 * @ORM\Table(name="attribute_group")
 */
class AttributeGroupEntity extends AbstractTranslatableEntity {

    /**
     * @var string
     */
    protected $_translationEntity = 'Ffb\Backend\Entity\AttributeGroupLangEntity';

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="AttributeGroupAttributeEntity", mappedBy="attributeGroup", cascade={"persist", "remove"}, orphanRemoval=true, fetch="LAZY")
     * @ORM\OrderBy({"sort" = "ASC"})
     */
    protected $attributeGroupAttributes;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="AttributeGroupLangEntity", mappedBy="translationTarget", cascade={"persist", "remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $translations;

    /**
     * Construct
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Clone
     */
    public function __clone() {
        parent::__clone();

        if ($this->attributeGroupAttributes) {
            $attributeGroupAttributes = new Collections\ArrayCollection();
            foreach($this->attributeGroupAttributes as $attributeGroupAttribute) {
                $attributeGroupAttribute = clone $attributeGroupAttribute;
                $attributeGroupAttribute->setAttributeGroup($this);
                $attributeGroupAttributes->add($attributeGroupAttribute);
            }
            $this->attributeGroupAttributes = $attributeGroupAttributes;
        } else {
            $this->attributeGroupAttributes = new Collections\ArrayCollection();
        }

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
            $attributeGroupAttribute->setAttributeGroup($this);
            $this->attributeGroupAttributes->add($attributeGroupAttribute);
        }
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $attributeGroupAttributes
     */
    public function removeAttributeGroupAttributes(\Doctrine\Common\Collections\Collection $attributeGroupAttributes) {
        foreach ($attributeGroupAttributes as $attributeGroupAttribute) {
            $attributeGroupAttribute->setAttributeGroup(null);
            $this->attributeGroupAttributes->removeElement($attributeGroupAttribute);
        }
    }

}
