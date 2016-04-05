<?php

namespace Ffb\Backend\Entity;

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;

/**
 * ProductGroupEntity
 *
 * @ORM\Entity
 * @ORM\Table(name="product_group")
 */
class ProductGroupEntity extends AbstractTranslatableEntity {

    /**
     * @var string
     */
    protected $_translationEntity = 'Ffb\Backend\Entity\ProductGroupLangEntity';

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var ProductGroupEntity
     * @ORM\OneToOne(targetEntity="ProductGroupEntity")
     * @ORM\JoinColumn(name="product_group_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="ProductGroupEntity", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $childeren;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="ProductGroupLangEntity", mappedBy="translationTarget", cascade={"persist", "remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $translations;

    /**
     * Construct
     */
    public function __construct() {
        parent::__construct();

        $this->childeren = new Collections\ArrayCollection();
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

//    /**
//     * @return ProductGroupEntity
//     */
//    function getProductGroup() {
//        return $this->productGroup;
//    }
//
//    /**
//     * @param \Ffb\Backend\Entity\ProductGroupEntity $productGroup
//     */
//    function setProductGroup(ProductGroupEntity $productGroup) {
//        $this->productGroup = $productGroup;
//    }

    /**
     * @return ProductGroupEntity
     */
    function getParent() {
        return $this->parent;
    }

    /**
     * @param \Ffb\Backend\Entity\ProductGroupEntity $parent
     */
    function setParent(ProductGroupEntity $parent) {
        $this->parent = $parent;
    }

    /**
     * @return Collections\ArrayCollection
     */
    function getChilderen() {
        return $this->childeren;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $childeren
     */
    function setChilderen(\Doctrine\Common\Collections\ArrayCollection $childeren) {
        $this->childeren = $childeren;
    }

}
