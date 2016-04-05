<?php

namespace Ffb\Backend\Entity;

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;

/**
 * ProductCategoryEntity
 *
 * @ORM\Entity
 * @ORM\Table(name="product_category")
 */
class ProductCategoryEntity extends \Ffb\Common\Entity\AbstractBaseEntity {

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
     * @var ProductEntity
     * @ORM\ManyToOne(targetEntity="ProductEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $product;

    /**
     * @var CategoryEntity
     * @ORM\ManyToOne(targetEntity="CategoryEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;

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
     * @return ProductEntity
     */
    function getProduct() {
        return $this->product;
    }

    /**
     * @param \Ffb\Backend\Entity\ProductEntity $product
     */
    function setProduct(ProductEntity $product) {
        $this->product = $product;
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
    function setCategory(CategoryEntity $category) {
        $this->category = $category;
    }

}