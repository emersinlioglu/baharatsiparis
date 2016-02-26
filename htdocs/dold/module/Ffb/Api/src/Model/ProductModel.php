<?php

namespace Ffb\Api\Model;

use \Ffb\Backend\Entity;
use \Zend\ServiceManager;
use \Zend\Log\Logger;
use \Ffb\Backend\Model\AttributeValueModel;

/**
 * @author erdal.mersinlioglu
 */
class ProductModel extends \Ffb\Api\Model\AbstractBaseModel {

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param \Zend\Log\Logger $logger
     * @param Entity\UserEntity $identity
     */
    public function __construct(ServiceManager\ServiceLocatorInterface $sl, Logger $logger = null, Entity\UserEntity $identity = null) {
        $this->_entityClass = 'Ffb\Backend\Entity\ProductEntity';
        parent::__construct($sl, $logger, $identity);
    }

    /**
     * Find products that are not default category product
     * @param array $params
     * @return array
     */
    public function findAllProducts(array $params = array()) {

        $qb = $this->getRepository()->createQueryBuilder('product');
        $qb->leftJoin('product.category', 'defaultProductCategory');

        $qb->leftJoin('product.productCategories', 'productCategory');
        $qb->leftJoin('productCategory.category', 'catgory');

        // only parent products
        $qb->andwhere($qb->expr()->isNull('product.parent'));

        // no root products
        $qb->andWhere($qb->expr()->isNull('defaultProductCategory'));

        // default lang
        //$qb->andWhere($qb->expr()->andX('productLang.lang = ' . Entity\LangEntity::DEFAULT_LANGUAGE_ID));

        if (is_array($params)) {
            foreach ($params as $param => $value) {
                switch ($param) {
                    //case 'search':
                    //    $qb->leftJoin('product.translations', 'productLang');
                    //    $qb->andWhere($qb->expr()->like('productLang.name', "'%$value%'"));
                    //    break;
                    //case 'isSystem':
                    //    $qb->andWhere($qb->expr()->eq('product.isSystem', $value));
                    //    break;
                    case 'categoryId':
                        if ($value) {
                            $qb->andWhere($qb->expr()->eq('catgory', $value));
                        }
                        break;
                    default:
                        break;
                }
            }
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $language
     * @param $categoryId
     * @return array
     */
    public function getCategoryProductsData($language, $categoryId, $count, $offset) {

        // find products
        $products = $this->findAllProducts(array(
            'categoryId' => $categoryId
        ));

        // count
        $count = count($products);

        // products data
        $productsData = array();
        foreach ($products as $product) {
            $productsData[] = $this->_getProductData($product, $language);
        }

        // result
        $result = array(
            'count' => $count,
            'products' => $productsData
        );

        return $result;
    }

    /**
     * Returns product details data
     * @param $language
     * @param $productId
     * @return array
     */
    public function getProductsDetailsData($language, $productId) {

        $product = $this->findById((int)$productId);
        $result = array();

        if ($product) {
            $withAllAttributes = true;
            $result['product'] = $this->_getProductData($product, $language, $withAllAttributes);
        }

        return $result;
    }

    /**
     * Product data
     *
     * array(
     *     'id' => 1,
     *     'name' => 'Product',
     *     'image' => 'url',
     *     'attributeGroups' => array(),
     *     'variants' => array()
     * )
     *
     * @param Entity\ProductEntity $product
     * @param $language
     * @param bool $withAllAttributes
     * @return array
     */
    private function _getProductData(Entity\ProductEntity $product, $language, $withAllAttributes = false) {

        // name
        $name = $product->getCurrentTranslation($language)->getName();
        if (!$name) {
            $name = $product->getCurrentTranslation($this->getMasterLanguage())->getName();
        }

        $result = array(
            'id'        => $product->getId(),
            'name'      => $name,
            'isSystem'  => $product->getIsSystem(),
            'image'     => $this->_getFirstImage($product, $language)
        );

        if ($withAllAttributes) {
            $result['attributeGroups'] = $this->_getAttributeGroupsData($product, $language);
        }

        $result['variants'] = $this->_getVariantsData($product, $language);

        return $result;
    }

    /**
     * Product variants data
     * @param Entity\ProductEntity $product
     * @param $language
     * @return array
     */
    private function _getVariantsData(Entity\ProductEntity $product, $language) {
        $result = array();

        foreach ($product->getChilderen() as $variant) {
            // name
            $name = $variant->getCurrentTranslation($language)->getName();
            if (!$name) {
                $name = $variant->getCurrentTranslation($this->getMasterLanguage())->getName();
            }

            $result[] = array(
                'id'     => $variant->getId(),
                'name'   => $name,
                'image'  => $this->_getFirstImage($variant, $language)
            );
        }

        return $result;
    }

    /**
     * Attribute groups data
     * @param Entity\ProductEntity $product
     * @param $language
     * @return array
     */
    private function _getAttributeGroupsData(Entity\ProductEntity $product, $language) {
        $result = array();

        $backendProductModel = new \Ffb\Backend\Model\ProductModel($this->_sl);

        /* @var $template Entity\TemplateEntity */
        $template = $backendProductModel->getProductTemplate($product/*, $category*/);

        if ($template) {
            foreach ($template->getTemplateAttributeGroups() as $templateAttributeGroup) {
                $attributeGroup = $templateAttributeGroup->getAttributeGroup();

                // attribute group name
                $name = $attributeGroup->getCurrentTranslation($language)->getName();
                if (!$name) {
                    $name = $attributeGroup->getCurrentTranslation($this->getMasterLanguage())->getName();
                }

                // attribute group data
                $agData = array(
                    'id' => $attributeGroup->getId(),
                    'name' => $name,
                    'attributes' => array()
                );

                foreach ($attributeGroup->getAttributeGroupAttributes() as $attributeGroupAttribute) {
                    $attribute = $attributeGroupAttribute->getAttribute();

                    // attribute name
                    $name = $attribute->getCurrentTranslation($language)->getName();
                    if (!$name) {
                        $name = $attribute->getCurrentTranslation($this->getMasterLanguage())->getName();
                    }

                    // unit
                    $unit = $attribute->getCurrentTranslation($language)->getUnit();
                    if (!$unit) {
                        $unit = $attribute->getCurrentTranslation($this->getMasterLanguage())->getUnit();
                    }

                    // attribute data
                    $attributeData = array(
                        'id' => $attribute->getId(),
                        'name' => $name,
                        'unit' => $unit,
                        'type' => $attribute->getType(),
                        'value' => $this->_getAttributeValue($product, $attributeGroup, $attribute, $language)
                    );

                    // add attribute
                    $agData['attributes'][] = $attributeData;
                }

                // add attribbute group data to list
                $result[] = $agData;
            }
        }

        return $result;
    }

    /**
     * @param Entity\ProductEntity $product
     * @param Entity\AttributeGroupEntity $attributeGroup
     * @param Entity\AttributeEntity $attribute
     * @param $language
     * @return array|string
     */
    private function _getAttributeValue(
        Entity\ProductEntity $product,
        Entity\AttributeGroupEntity $attributeGroup,
        Entity\AttributeEntity $attribute,
        $language
    ) {

        // params
        $result = null;

        // get model(s)
        $attributeValueModel = new AttributeValueModel($this->_sl);

        // get entity
        $attributeValue = $attributeValueModel->findByAtributeProductAndLanguage($product, $attribute, $language);
        if (is_null($attributeValue)) {
            return '';
        }

        // parent product
        $parentProduct = $product->getParent();

        // attributes for the actual translation
        $attributeGroupId = $attributeGroup->getId();
        $attributeLangId  = $attributeValue->getAttributeLang()->getId();
        $langId           = $attributeValue->getAttributeLang()->getLang()->getId();

        // values
        // (1. level) standard
        $value    = $attributeValue->getValue();
        $valueMin = $attributeValue->getValueMin();
        $valueMax = $attributeValue->getValueMax();
        $attributeValueId = $attributeValue->getId();

        if ($attributeValue->getIsInherited()) {

            $inheritFromCategoryProduct = false;

            // parent product value
            if ($parentProduct) {
                $parentAttributeValue = $attributeValueModel->findParentAttributeValue(
                    $parentProduct,
                    $attributeGroupId,
                    $attributeLangId,
                    $langId
                );
                if ($parentAttributeValue && !$parentAttributeValue->getIsInherited()) {

                    // (2. level) parent product
                    $value    = $parentAttributeValue->getValue();
                    $valueMin = $parentAttributeValue->getValueMin();
                    $valueMax = $parentAttributeValue->getValueMax();
                    $attributeValueId = $parentAttributeValue->getId();

                } else {
                    $inheritFromCategoryProduct = true;
                }
            }

            // categoryProduct value
            if ($inheritFromCategoryProduct || !$parentProduct) {

                // get default category product
                $defaultCategoryProduct = null;
                if ($productCategory = $product->getProductCategories()->first()) {
                    $pCategory = $productCategory->getCategory();
                    if ($pCategory && $pCategory->getDefaultProduct()) {
                        $defaultCategoryProduct = $pCategory->getDefaultProduct();
                    }
                }

                // (3. level) default category product
                $defaultCategoryProductAttributeValue = $attributeValueModel->findParentAttributeValue(
                    $defaultCategoryProduct,
                    $attributeGroupId,
                    $attributeLangId,
                    $langId
                );
                if ($defaultCategoryProductAttributeValue) {
                    $value    = $defaultCategoryProductAttributeValue->getValue();
                    $valueMin = $defaultCategoryProductAttributeValue->getValueMin();
                    $valueMax = $defaultCategoryProductAttributeValue->getValueMax();
                    $attributeValueId = $defaultCategoryProductAttributeValue->getId();
                }
            }
        }

        switch ($attribute->getType()) {
            case Entity\AttributeEntity::TYPE_VARCHAR:
            case Entity\AttributeEntity::TYPE_TEXT:
            case Entity\AttributeEntity::TYPE_INT:
            case Entity\AttributeEntity::TYPE_FLOAT:
            case Entity\AttributeEntity::TYPE_BOOL:

                $result = $value;
                break;

            case Entity\AttributeEntity::TYPE_IMAGE:
            case Entity\AttributeEntity::TYPE_DOCUMENT:

                // get uploaded files
                $destination  = null;
                if ($attributeValueId) {
                    switch ($attribute->getType()) {
                        case Entity\AttributeEntity::TYPE_IMAGE:
                            $destination = 'image';
                            break;
                        case Entity\AttributeEntity::TYPE_DOCUMENT:
                            $destination = 'file';
                            break;
                        default:
                            break;
                    }
                    $result = $attributeValueModel->getFilesForUploadInput($attributeValueId, $destination);
                }
                break;

            case Entity\AttributeEntity::TYPE_RANGE_FLOAT:
            case Entity\AttributeEntity::TYPE_RANGE_INT:
                $result = array(
                    'min' => $valueMin,
                    'max' => $valueMax,
                );
                break;

            case Entity\AttributeEntity::TYPE_SELECT:

                $result = AttributeValueModel::parseOptionValues($value);
                break;

            default:
                break;
        }

        return $result;
    }

    /**
     * Returns the first image
     * @param Entity\ProductEntity $product
     * @param $language
     * @return array
     */
    private function _getFirstImage(Entity\ProductEntity $product, $language) {
        $result = '';

        $backendProductModel = new \Ffb\Backend\Model\ProductModel($this->_sl);

        /* @var $template Entity\TemplateEntity */
        $template = $backendProductModel->getProductTemplate($product/*, $category*/);

        if ($template) {
            foreach ($template->getTemplateAttributeGroups() as $templateAttributeGroup) {
                $attributeGroup = $templateAttributeGroup->getAttributeGroup();

                foreach ($attributeGroup->getAttributeGroupAttributes() as $attributeGroupAttribute) {
                    $attribute = $attributeGroupAttribute->getAttribute();

                    if ($attribute->getType() !== Entity\AttributeEntity::TYPE_IMAGE) {
                        continue;
                    }

                    // get images
                    $images = $this->_getAttributeValue($product, $attributeGroup, $attribute, $language);

                    if (is_array($images) && count($images) > 0) {
                        $image = reset($images);
                        $result = $this->_getHost() . $image['url'];
                    }
                }

            }
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function _getHost() {
        return $this->_host;
    }

}