<?php

namespace Ffb\Backend\Entity;

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;

/**
 * CategoryEntity
 *
 * @ORM\Entity
 * @ORM\Table(name="category")
 */
class CategoryEntity extends AbstractTranslatableEntity {

    /**
     * @var string
     */
    protected $_translationEntity = 'Ffb\Backend\Entity\CategoryLangEntity';

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
     * @var TemplateEntity
     * @ORM\OneToOne(targetEntity="TemplateEntity")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;

    /**
     * @var CategoryEntity
     * @ORM\OneToOne(targetEntity="CategoryEntity")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OrderBy({"sort" = "ASC"})
     * @ORM\OneToMany(targetEntity="CategoryEntity", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $childeren;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="CategoryLangEntity", mappedBy="translationTarget", cascade={"persist", "remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $translations;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="ProductCategoryEntity", mappedBy="category", cascade={"persist", "remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $productCategories;

    /**
     * Root-Product for Default-Attribute-Values
     *
     * @var ProductEntity
     * @ORM\OneToOne(targetEntity="ProductEntity", inversedBy="category", cascade={"persist"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=true)
     */
    protected $defaultProduct = null;

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
     * @return TemplateEntity
     */
    function getTemplate() {
        return $this->template;
    }

    /**
     * @param \Ffb\Backend\Entity\TemplateEntity $template
     */
    function setTemplate(TemplateEntity $template = null) {
        $this->template = $template;
    }

    /**
     * @return CategoryEntity
     */
    function getParent() {
        return $this->parent;
    }

    /**
     * @param \Ffb\Backend\Entity\CategoryEntity $parent
     */
    function setParent(CategoryEntity $parent = null) {
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

    /**
     * @return Collections\ArrayCollection
     */
    function getProductCategories() {
        return $this->productCategories;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $productCategories
     */
    function setProductCategories(\Doctrine\Common\Collections\ArrayCollection $productCategories) {
        $this->productCategories = $productCategories;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $productCategories
     */
    public function addProductCategories(\Doctrine\Common\Collections\Collection $productCategories) {
        foreach ($productCategories as $productCategory) {
            $productCategory->setCategory($this);
            $this->productCategories->add($productCategory);
        }
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $productCategories
     */
    public function removeProductCategories(\Doctrine\Common\Collections\Collection $productCategories) {
        foreach ($productCategories as $productCategory) {
            $productCategory->setAttributeGroup(null);
            $this->productCategories->removeElement($productCategory);
        }
    }

    /**
     * @return ProductEntity
     */
    function getDefaultProduct() {
        return $this->defaultProduct;
    }

    /**
     * @param \Ffb\Backend\Entity\ProductEntity $product
     */
    function setDefaultProduct(ProductEntity $product = null) {
        $this->defaultProduct = $product;
    }

}
