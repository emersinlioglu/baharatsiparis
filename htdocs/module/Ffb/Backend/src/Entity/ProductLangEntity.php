<?php

namespace Ffb\Backend\Entity;

use \Ffb\Backend\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections;

/**
 * AttributeLang
 * This class extends the common FFB AbstractBaseEntity class!
 *
 * @ORM\Entity
 * @ORM\Table(name="product_lang")
 */
class ProductLangEntity extends Entity\AbstractTranslationEntity {

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
     * @ORM\Column(name="alias", type="string", length=256, nullable=true)
     */
    protected $alias;

    /**
     * @var string
     * @ORM\Column(name="description", type="string", length=256, nullable=true)
     */
    protected $description;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=256, nullable=true)
     */
    protected $name;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="AttributeValueEntity", mappedBy="productLang", cascade={"persist", "remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $attributeValues;

    /**
     * @var AttributeEntity
     * @ORM\ManyToOne(targetEntity="ProductEntity")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $translationTarget;

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
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
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
     * @return Collections\ArrayCollection
     */
    function getAttributeValues() {
        return $this->attributeValues;
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
            $attributeValue->setProductLang($this);
            $this->attributeValues->add($attributeValue);
        }
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $attributeValues
     */
    public function removeAttributeValues(\Doctrine\Common\Collections\Collection $attributeValues) {
        foreach ($attributeValues as $attributeValue) {
            $attributeValue->setProductLang(null);
            $this->attributeValues->removeElement($attributeValue);
        }
    }

}