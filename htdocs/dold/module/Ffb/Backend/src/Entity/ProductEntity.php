<?php

namespace Ffb\Backend\Entity;

use Doctrine\Common\Collections;
use \Ffb\Backend\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * ProductEntity
 * This class extends the common FFB AbstractUserOwnedEntity class!
 *
 * @ORM\Entity
 * @ORM\Table(name="product")
 */
class ProductEntity extends Entity\AbstractTranslatableEntity {

    /**
     * @var string
     */
    const TYPE_LINKED_PRODUCT = 'linkedProduct';

    /**
     * @var string
     */
    const TYPE_ACCESSORY_PRODUCT = 'accessoryProduct';

    /**
     * @var string
     */
    protected $_translationEntity = 'Ffb\Backend\Entity\ProductLangEntity';

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="number", type="string", options={"default":255, "unsigned":true})
     */
    protected $number;

    /**
     * @var float
     * @ORM\Column(name="price", type="float", options={"default":255, "unsigned":true})
     */
    protected $price = 0.0;

    /**
     * @var boolean
     * @ORM\Column(name="online", type="boolean", options={"default": false})
     */
    protected $online = false;

    /**
     * @var int
     * @ORM\Column(name="sort", type="integer", nullable=false, options={"default": 0, "unsigned":true})
     */
    protected $sort = 0;

    /**
     * @var bool
     * @ORM\Column(name="is_system", type="boolean", options={"default": false})
     */
    protected $isSystem = false;


    /**
     * @var string
     * @ORM\Column(name="image_url", type="string", options={"default":256})
     */
    protected $imageUrl;

    /**
     * @var ProductEntity
     * @ORM\ManyToOne(targetEntity="ProductEntity")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="ProductEntity", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $childeren;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="ProductLangEntity", mappedBy="translationTarget", cascade={"persist", "remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $translations;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="ProductGroupProductEntity", mappedBy="product", cascade={"persist", "remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $productGroupProducts;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="ProductCategoryEntity", mappedBy="product", cascade={"persist", "remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $productCategories;

    /**
     * This association is only for the assignment of default product to category.
     *
     * @var CategoryEntity
     * @ORM\OneToOne(targetEntity="CategoryEntity", mappedBy="defaultProduct", cascade={"persist"}, fetch="LAZY")
     */
    protected $category;

    /**
     * @ORM\ManyToMany(targetEntity="ProductEntity")
     * @ORM\JoinTable(
     *      name="linked_product",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="linked_product_id", referencedColumnName="id")}
     * )
     */
    protected $linkedProducts;

    /**
     * @ORM\ManyToMany(targetEntity="ProductEntity")
     * @ORM\JoinTable(
     *      name="accessory_product",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="accessory_product_id", referencedColumnName="id")}
     * )
     */
    protected $accessoryProducts;

    /**
     * @ORM\ManyToMany(targetEntity="CategoryEntity")
     * @ORM\JoinTable(
     *      name="multiple_usage",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
     * )
     */
    protected $multipleUsages;

    public function __construct() {
        parent::__construct();

        $this->productGroupProducts = new Collections\ArrayCollection();
        $this->productCategories    = new Collections\ArrayCollection();
        $this->childeren            = new Collections\ArrayCollection();
        $this->linkedProducts       = new Collections\ArrayCollection();
        $this->accessoryProducts    = new Collections\ArrayCollection();
        $this->multipleUsages       = new Collections\ArrayCollection();
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
    public function getNumber() {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number) {
        $this->number = $number;
    }

    /**
     * @return float
     */
    public function getPrice() {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price) {
        $this->price = $price;
    }

    /**
     * @return boolean
     */
    public function isOnline() {
        return $this->online;
    }

    /**
     * @param boolean $online
     */
    public function setOnline($online) {
        $this->online = $online;
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
     * @return bool
     */
    function getIsSystem() {
        return $this->isSystem;
    }

    /**
     * @param bool $isSystem
     */
    function setIsSystem($isSystem) {
        $this->isSystem = $isSystem;
    }

    /**
     * @return string
     */
    public function getImageUrl() {
        return $this->imageUrl;
    }

    /**
     * @param string $imageUrl
     */
    public function setImageUrl($imageUrl) {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @return CategoryEntity
     */
    function getCategory() {
        return $this->category;
    }

    /**
     * @param \Ffb\Backend\Entity\CategoryEntity $category
     */
    function setCategory(CategoryEntity $category = null) {
        $this->category = $category;
    }

    /**
     * @return ProductEntity
     */
    function getParent() {
        return $this->parent;
    }

    /**
     * @param \Ffb\Backend\Entity\ProductEntity $parent
     */
    function setParent(ProductEntity $parent = null) {
        $this->parent = $parent;
    }

    /**
     * @param Collections\ArrayCollection $translations
     * @return AbstractBaseEntity|void
     */
    public function setTranslations($translations) {
        $this->translations = $translations;
    }

    /**
     * @return Collections\ArrayCollection
     */
    public function getProductGroupProducts() {
        return $this->productGroupProducts;
    }

    /**
     * @param Collections\ArrayCollection $productGroupProducts
     */
    public function setProductGroupProducts($productGroupProducts) {
        $this->productGroupProducts = $productGroupProducts;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $productGroupProducts
     */
    public function addProductGroupProducts(\Doctrine\Common\Collections\Collection $productGroupProducts) {
        foreach ($productGroupProducts as $productGroupProduct) {
            $productGroupProduct->setProduct($this);
            $this->productGroupProducts->add($productGroupProduct);
        }
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $productGroupProducts
     */
    public function removeProductGroupProducts(\Doctrine\Common\Collections\Collection $productGroupProducts) {
        foreach ($productGroupProducts as $productGroupProduct) {
            $productGroupProduct->setProduct(null);
            $this->productGroupProducts->removeElement($productGroupProduct);
        }
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
            $productCategory->setProduct($this);
            $this->productCategories->add($productCategory);
        }
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $productCategories
     */
    public function removeProductCategories(\Doctrine\Common\Collections\Collection $productCategories) {
        foreach ($productCategories as $productCategory) {
            $productCategory->setProduct(null);
            $this->productCategories->removeElement($productCategory);
        }
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
     * @param \Doctrine\Common\Collections\Collection $childeren
     */
    public function addChilderen(\Doctrine\Common\Collections\Collection $childeren) {
        foreach ($childeren as $child) {
            $child->setProduct($this);
            $this->childeren->add($child);
        }
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $childeren
     */
    public function removeChilderen(\Doctrine\Common\Collections\Collection $childeren) {
        foreach ($childeren as $child) {
            $child->setProduct(null);
            $this->childeren->removeElement($child);
        }
    }

    /**
     * @return Collections\ArrayCollection
     */
    function getLinkedProducts() {
        return $this->linkedProducts;
    }

    /**
     * @param Collections\ArrayCollection $linkedProducts
     */
    function setLinkedProducts(Collections\ArrayCollection $linkedProducts) {
        $this->linkedProducts = $linkedProducts;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $linkedProducts
     */
    public function addLinkedProducts(\Doctrine\Common\Collections\Collection $linkedProducts) {
        foreach ($linkedProducts as $linkedProduct) {
            $linkedProduct->setProduct($this);
            $this->linkedProducts->add($linkedProduct);
        }
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $linkedProducts
     */
    public function removeLinkedProducts(\Doctrine\Common\Collections\Collection $linkedProducts) {
        foreach ($linkedProducts as $linkedProduct) {
            $linkedProduct->setProduct(null);
            $this->linkedProducts->removeElement($linkedProduct);
        }
    }

    /**
     * @return Collections\ArrayCollection
     */
    function getAccessoryProducts() {
        return $this->accessoryProducts;
    }

    /**
     * @param Collections\ArrayCollection $accessoryProducts
     */
    function setAccessoryProducts(Collections\ArrayCollection $accessoryProducts) {
        $this->accessoryProducts = $accessoryProducts;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $accessoryProducts
     */
    public function addAccessoryProducts(\Doctrine\Common\Collections\Collection $accessoryProducts) {
        foreach ($accessoryProducts as $accessoryProduct) {
            $accessoryProduct->setProduct($this);
            $this->accessoryProducts->add($accessoryProduct);
        }
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $accessoryProducts
     */
    public function removeAccessoryProducts(\Doctrine\Common\Collections\Collection $accessoryProducts) {
        foreach ($accessoryProducts as $accessoryProduct) {
            $accessoryProduct->setProduct(null);
            $this->accessoryProducts->removeElement($accessoryProduct);
        }
    }

    /**
     * @return Collections\ArrayCollection
     */
    public function getMultipleUsages() {
        return $this->multipleUsages;
    }

    /**
     * @param Collections\ArrayCollection $multipleUsages
     * @return Collections\ArrayCollection
     */
    public function setMultipleUsages(Collections\ArrayCollection $multipleUsages) {
        $this->multipleUsages = $multipleUsages;
        return $this;
    }

}
