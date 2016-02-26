<?php

namespace Ffb\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections;

/**
 * AttributeLang
 * This class extends the common FFB AbstractBaseEntity class!
 *
 * @ORM\Entity
 * @ORM\Table(name="attribute_lang")
 */
class AttributeLangEntity extends \Ffb\Backend\Entity\AbstractTranslationEntity {

    /**
     * id INT UNSIGNED NOT NULL
     *
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=256, nullable=true)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(name="title", type="string", length=256, nullable=true)
     */
    protected $title;

    /**
     * unit VARCHAR(256)
     *
     * @var string
     * @ORM\Column(name="unit", type="string", length=256, nullable=true)
     */
    protected $unit;

    /**
     * @var string
     * @ORM\Column(name="alias", type="string", length=256, nullable=true)
     */
    protected $alias;

    /**
     * @var AttributeEntity
     * @ORM\ManyToOne(targetEntity="AttributeEntity")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
     */
    protected $translationTarget;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="AttributeValueEntity", mappedBy="attributeLang", cascade={"persist", "remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $attributeValues;

    public function __construct() {

        $this->attributeValues = new Collections\ArrayCollection();
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
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    function getTitle() {
        return $this->title;
    }

    /**
     * @param string $title
     */
    function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getUnit() {
        return $this->unit;
    }

    /**
     * @param string $unit
     */
    public function setUnit($unit) {
        $this->unit = $unit;
    }

    /**
     * @return string
     */
    public function getAlias() {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias($alias) {
        $this->alias = $alias;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $attributeValues
     */
    function setAttributeValues(\Doctrine\Common\Collections\ArrayCollection $attributeValues) {
        $this->attributeValues = $attributeValues;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $attributeValues
     */
    public function addAttributeValues(\Doctrine\Common\Collections\Collection $attributeValues) {
        foreach ($attributeValues as $attributeValue) {
            $attributeValue->setAttributeLang($this);
            $this->attributeValues->add($attributeValue);
        }
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $attributeValues
     */
    public function removeAttributeValues(\Doctrine\Common\Collections\Collection $attributeValues) {
        foreach ($attributeValues as $attributeValue) {
            $attributeValue->setAttributeLang(null);
            $this->attributeValues->removeElement($attributeValue);
        }
    }


}