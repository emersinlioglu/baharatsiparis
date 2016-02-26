<?php

namespace Ffb\Backend\Entity;

use Doctrine\Common\Collections;
use \Ffb\Common\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * ProductGroupProductEntity
 *
 * @ORM\Entity
 * @ORM\Table(name="product_group_product")
 */
class ProductGroupProductEntity extends Entity\AbstractBaseEntity {

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var ProductEntity
     * @ORM\ManyToOne(targetEntity="ProductEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $product;

    /**
     * @var ProductGroupEntity
     * @ORM\ManyToOne(targetEntity="ProductGroupEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="product_group_id", referencedColumnName="id")
     */
    protected $productGroup;

    /**
     * @var int
     * @ORM\Column(name="sort", type="integer", nullable=false, options={"unsigned":true})
     */
    protected $sort = 0;

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
     * @return ProductEntity
     */
    public function getProduct() {
        return $this->product;
    }

    /**
     * @param ProductEntity $product
     */
    public function setProduct($product) {
        $this->product = $product;
    }

    /**
     * @return ProductGroupEntity
     */
    public function getProductGroup() {
        return $this->productGroup;
    }

    /**
     * @param ProductGroupEntity $productGroup
     */
    public function setProductGroup($productGroup) {
        $this->productGroup = $productGroup;
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


}