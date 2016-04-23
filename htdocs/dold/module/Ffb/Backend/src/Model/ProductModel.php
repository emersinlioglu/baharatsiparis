<?php

namespace Ffb\Backend\Model;

use Ffb\Backend\Entity;
use \Ffb\Common\I18n\Translator\Translator;
use \Ffb\Backend\View\Helper;

use Zend\ServiceManager;
use Doctrine\Common\Collections;

/**
 * @author erdal.mersinlioglu
 */
class ProductModel extends AbstractBaseModel {

    /**
     * Master language code
     * @var string
     */
    protected $_masterLang = '';

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param \Zend\Log\Logger $logger
     * @param Entity\UserEntity $identity
     */
    public function __construct(
            ServiceManager\ServiceLocatorInterface $sl,
            \Zend\Log\Logger $logger = null,
            Entity\UserEntity $identity = null) {
        $this->_entityClass = 'Ffb\Backend\Entity\ProductEntity';
        parent::__construct($sl, $logger, $identity);

        $moduleConf = $this->_sl->get('Config');
        // masterLang
        $this->_masterLang = $moduleConf['translator']['master_language_code'];
    }

    /**
     * Builds product
     *
     * @param array $data
     * @return Entity\ProductEntity
     */
    public function build(array $data) {

        $langModel = new LangModel($this->_sl);

        $product = new Entity\ProductEntity();
        $translations = new Collections\ArrayCollection();
        $productCategories = new Collections\ArrayCollection();

        foreach ($data as $key => $value) {
            switch($key){
                case 'translations':
                    foreach ($value as $langIso => $productLangData) {
                        $lang = $langModel->findOneBy(array(
                            'iso' => $langIso
                        ));
                        $productLang = new Entity\ProductLangEntity();
                        $productLang->setLang($lang);
                        $productLang->setName($productLangData['name']);
                        $translations->add($productLang);
                    }
                    break;
                case 'parent':
                    $product->setParent($value);
                    break;
                case 'price':
                    $product->setPrice((float)$value);
                    break;
                case 'amount':
                    $product->setAmount($value);
                    break;
                case 'categories':
                    foreach ($value as $key => $category) {
                        $productCategory = new Entity\ProductCategoryEntity();
                        $productCategory->setProduct($product);
                        $productCategory->setCategory($category);
                        $productCategories->add($productCategory);
                    }
                    break;
            }
        }

        $product->addTranslations($translations);
        $product->addProductCategories($productCategories);

        return $product;
    }

    /**
     * Creates default product for given category
     *
     * @param Entity\CategoryEntity $category
     * @return Entity\ProductEntity
     * @throws \Exception
     */
    public function buildDefaultForCategory(Entity\CategoryEntity $category) {

        // model(s)
        $categoryModel = new CategoryModel($this->_sl, $this->_logger);

        // create new attribute
        $product = new Entity\ProductEntity();

        // add AttributeGroupAttribute, if attributeGroupId is given
        $productCategory = new Entity\ProductCategoryEntity();
        $productCategory->setCategory($category);
        $product->addProductCategories(new Collections\ArrayCollection(array($productCategory)));

        // createMissingTranslations
        $this->createMissingTranslations($product, 'Ffb\Backend\Entity\ProductLangEntity');

        // createMissingAttributeValues and persist.
        // attributeValues must be persisted
        // so that the form can work with atrribute value ids
        $this->createMissingAttributeValues($product/*, $category*/);

        // update
        $this->insert($product);

        $category->setDefaultProduct($product);
        $categoryModel->update($category);

        return $product;
    }

    /**
     * Returns product template
     *
     * @param Entity\ProductEntity $product
     * @return Entity\TemplateEntity
     */
    public function getProductTemplate(Entity\ProductEntity $product = null/*, Entity\CategoryEntity $category = null*/) {

        $template = null;
        $category = null;
        $categoryModel = new CategoryModel($this->_sl, $this->_logger);

        if ($product && $product->getId()) {
            $category = $this->getProductCategory($product);
        }

        if ($category) {
            $template = $categoryModel->getAggregatedTemplate($category);
        }

        return $template;
    }

    /**
     * Returns the product category
     *
     * @param \Ffb\Backend\Entity\ProductEntity $product
     * @return Entity\CategoryEntity
     */
    public function getProductCategory(Entity\ProductEntity $product) {

        $category = null;
        $productCategory = $product->getProductCategories()->first();
        if ($productCategory) {
            $category = $productCategory->getCategory();
        }

        return $category;
    }

    /**
     * Returns product template attributes in the right sort order
     *
     * $result = array(
     *      'attributeGroupId' => array(
     *          'name' => 'Attribute Group Name',
     *          'attributes' => array(
     *              'attributeId' => 'Attribute Name'
     *          )
     *      )
     * )
     *
     * @param Entity\ProductEntity $product
     * @return array
     */
    public function getAttributeGroupsData(Entity\ProductEntity $product = null/*, Entity\CategoryEntity $category = null*/) {

        $result = array();
        /* @var $template Entity\TemplateEntity */
        $template = $this->getProductTemplate($product/*, $category*/);

        if ($template) {
            foreach ($template->getTemplateAttributeGroups() as $templateAttributeGroup) {
                $attributeGroup = $templateAttributeGroup->getAttributeGroup();

                $attributes = array();
                foreach ($attributeGroup->getAttributeGroupAttributes() as $attributeGroupAttribute) {
                    $attribute = $attributeGroupAttribute->getAttribute();
                    $attributes[$attribute->getId()] = $attribute;
                }

                $result[$attributeGroup->getId()]['name'] = $attributeGroup->getCurrentTranslation()->getName();
                $result[$attributeGroup->getId()]['attributes'] = $attributes;
            }
        }

        return $result;
    }

    /**
     * Creates missing attributeValues
     *
     * @param Entity\ProductEntity $product
     * @return Entity\ProductEntity
     */
    public function createMissingAttributeValues(Entity\ProductEntity $product/*, Entity\CategoryEntity $category = null*/) {

        // get model(s)
        $attributeLangModel  = new AttributeLangModel($this->_sl, $this->_logger);
        $attributeGroupModel = new AttributeGroupModel($this->_sl, $this->_logger);

        $attributeGroupsData = $this->getAttributeGroupsData($product/*, $category*/);

        // attributeGroups
        foreach ($attributeGroupsData as $attributeGroupId => $attributeGroupData) {

            $attributeGroup = $attributeGroupModel->findById($attributeGroupId);

            // attributes
            foreach ($attributeGroupData['attributes'] as $attributeId => $attributeName) {

                // check if attribute exists
                /* @var $productLang \Ffb\Backend\Entity\ProductLangEntity */
                foreach ($product->getTranslations() as $productLang) {

                    $hasAttribute = false;
                    $langId = $productLang->getLang()->getId();
                    foreach ($productLang->getAttributeValues() as $attributeValue) {

                        if ($attributeValue->getAttributeGroup()
                            && $attributeGroupId == $attributeValue->getAttributeGroup()->getId()
                            && $attributeValue->getAttributeLang()
                            && $attributeId == $attributeValue->getAttributeLang()->getTranslationTarget()->getId()
                        ) {
                            $hasAttribute = true;
                        }
                    }

                    // add AttributeValue for this translation
                    if (!$hasAttribute) {
                        $attributeValues = new \Doctrine\Common\Collections\ArrayCollection();
                        $attributeValue  = new Entity\AttributeValueEntity();
                        $attributeLang = $attributeLangModel->findOneBy(array(
                            'lang' => $langId,
                            'translationTarget' => $attributeId
                        ));

                        $attributeValue->setAttributeLang($attributeLang);
                        $attributeValue->setAttributeGroup($attributeGroup);
                        $attributeValues->add($attributeValue);
                        $productLang->addAttributeValues($attributeValues);
                    }
                }
            }
        }

        return $product;
    }

    /**
     * Add missing attribute value
     *
     * usage
     *   //                // add missing attribute value
     *   //                $this->_addMissingAttributeValue(
     *   //                        $product,
     *   //                        $attributeGroup,
     *   //                        $attributeLangModel,
     *   //                        $attributeId,
     *   //                        $attributeGroupId
     *   //                    );
     *
     * @param \Ffb\Backend\Entity\ProductEntity $product
     * @param \Ffb\Backend\Entity\AttributeGroupEntity $attributeGroup
     * @param \Ffb\Backend\Model\AttributeLangModel $attributeLangModel
     * @param int $attributeId
     * @param int $attributeGroupId
     */
    private function _addMissingAttributeValue(
        Entity\ProductEntity $product,
        Entity\AttributeGroupEntity $attributeGroup,
        AttributeLangModel $attributeLangModel,
        $attributeId,
        $attributeGroupId
    ) {

        // check if attribute exists
        /* @var $productLang \Ffb\Backend\Entity\ProductLangEntity */
        foreach ($product->getTranslations() as $productLang) {

            $hasAttribute = false;
            $langId = $productLang->getLang()->getId();
            foreach ($productLang->getAttributeValues() as $attributeValue) {

                if ($attributeValue->getAttributeGroup()
                    && $attributeGroupId == $attributeValue->getAttributeGroup()->getId()
                    && $attributeValue->getAttributeLang()
                    && $attributeId == $attributeValue->getAttributeLang()->getTranslationTarget()->getId()
                ) {
                    $hasAttribute = true;
                }
            }

            // add AttributeValue for this translation
            if (!$hasAttribute) {
                $attributeValues = new \Doctrine\Common\Collections\ArrayCollection();
                $attributeValue  = new Entity\AttributeValueEntity();
                $attributeLang = $attributeLangModel->findOneBy(array(
                    'lang' => $langId,
                    'translationTarget' => $attributeId
                ));

                $attributeValue->setAttributeLang($attributeLang);
                $attributeValue->setAttributeGroup($attributeGroup);
                $attributeValues->add($attributeValue);
                $productLang->addAttributeValues($attributeValues);
            }
        }
    }

    /**
     * Returns the category tree for the products
     *
     * @return array
     */
    public function getCategoryTree() {

        $categoryModel = new CategoryModel($this->_sl, $this->_logger);

        // prepare category items
        $items = array();
        foreach ($categoryModel->getFirstLevelProductCategories() as $category) {
            $items[] = $this->getCategoryData($category);
        }

        return $items;
    }

    /**
     * Returns categoriesData recursively with sub categories
     *
     * $result = array(
     *      'link'          => (string),
     *      'hasSubitems'   => (boolean),
     *      'subitems'      => array(
     *          'link'          => (string),
     *          'hasSubitems'   => (boolean),
     *          'subitems'      => '...recursivly...'
     *      )
     * )
     *
     * @param \Ffb\Backend\Entity\CategoryEntity $category
     * @return array
     */
    public function getCategoryData(Entity\CategoryEntity $category) {

        // get model(s)
        $categoryModel       = new CategoryModel($this->_sl, $this->_logger);
        $langModel           = new LangModel($this->_sl, $this->_logger);
        $spanHelper          = new Helper\HtmlSpanHelper();
        $linkHelper          = new Helper\HtmlLinkHelper();
        $url                 = $this->_sl->get('ViewHelperManager')->get('url');

        // entities
        $trans            = $category->getCurrentTranslation();
        $subCategories    = $category->getChilderen();
        $hasSubCategories = count($subCategories) > 0;

        $masterTranslation = $category->getCurrentTranslation($this->_masterLang)->getName();
        $translations = array();
        foreach($langModel->getActiveLanguagesAsArray() as $langId => $langCode) {
            $translations[$langCode] = $category->getCurrentTranslation($langId)->getName();
        }

        // result
        $result = array(
            'link' => array(
                'masterTrans' => $masterTranslation,
                'translations' => $translations,
                'url' => $url('home/default', array(
                    'controller' => 'product',
                    'action'     => 'subnavi',
                    'param'      => 'category',
                    'value'      => $category->getId()
                )),
                'paneTitle' => '&nbsp;',
                'copyUrl'   => $url('home/default', array(
                    'controller' => 'category',
                    'action'     => 'copy',
                    'param'      => 'category',
                    'value'      => $category->getId()
                )),
                'deleteUrl' => $url('home/default', array(
                    'controller' => 'category',
                    'action'     => 'delete',
                    'param'      => 'category',
                    'value'      => $category->getId()
                ))
            ),
            'span' => array(
                'attributes' => array(
                    'data-form-url' => $url('home/default', array(
                        'controller' => 'category',
                        'action'     => 'form',
                        'param'      => 'category',
                        'value'      => $category->getId()
                    ))
                )
            ),
            'subitems' => array(),
            'hasSubitems' => $hasSubCategories,
        );

        if ($hasSubCategories) {
            foreach ($subCategories as $subCategory) {
                $result['subitems'][] = $this->getCategoryData($subCategory);
            }
        }

        return $result;
    }

    /**
     * Returns parent value options
     *
     * @param \Ffb\Backend\Entity\ProductEntity $product
     * @return array
     */
    public function getParentValueOptions(Entity\ProductEntity $product = null/*, Entity\CategoryEntity $category = null*/) {
        $options = array();

        $qb = $this->getRepository()->createQueryBuilder('product');
        $qb->leftJoin('product.translations', 'productLang');
        $qb->leftJoin('product.productCategories', 'productCategory');
        $qb->leftJoin('productCategory.category', 'category');
        $qb->leftJoin('product.category', 'rootProductCategory');

        // not self
        if ($product && $product->getId()) {
            $qb->andWhere('product <> :product');
            $qb->setParameter('product' , $product->getId());
        }

        // only parent products
        $qb->andWhere($qb->expr()->isNull('product.parent') );

        // no category default product
        $qb->andWhere($qb->expr()->isNull('rootProductCategory'));

        //if ($category) {
        //    $qb->where($qb->expr()->andX('category = :category'));
        //    $qb->setParameter('category', $category);
        //}

        foreach ($qb->getQuery()->getResult() as $product) {
            $options[] = array(
                'label' => $product->getCurrentTranslation()->getName(),
                'value' => $product->getId()
            );
        }

        return $options;
    }

    /**
     * Finds the entities for subnavi
     *
     * @param array $params
     * @return array
     */
    public function findForSubnavi($params) {

        $qb = $this->getRepository()->createQueryBuilder('product');
        $qb->leftJoin('product.translations', 'productLang');
        $qb->leftJoin('product.category', 'category');

        // only parent products
        $qb->where($qb->expr()->isNull('product.parent'));

        // no root products
        $qb->andWhere($qb->expr()->isNull('category'));

        // default lang
        $qb->andWhere($qb->expr()->andX('productLang.lang = ' . Entity\LangEntity::DEFAULT_LANGUAGE_ID));

        if (is_array($params)) {
            foreach ($params as $param => $value) {
                switch ($param) {
                    case 'search':
                        $qb->andWhere($qb->expr()->like('productLang.name', "'%$value%'"));
                        break;
                    case 'isSystem':
                        $qb->andWhere($qb->expr()->eq('product.isSystem', $value));
                        break;
                    default:
                        break;
                }
            }
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Finds products to link
     *
     * @param array $params
     * @return array
     */
    public function findForLinkedProducts(array $params) {

        $qb = $this->getRepository()->createQueryBuilder('product');
        $qb->leftJoin('product.translations', 'productLang');
        $qb->leftJoin('product.category', 'category');

        // only parent products
        $qb->where($qb->expr()->isNull('product.parent'));

        // no root products
        $qb->andWhere($qb->expr()->isNull('category'));

        // default lang
        $qb->andWhere($qb->expr()->andX('productLang.lang = ' . Entity\LangEntity::DEFAULT_LANGUAGE_ID));

        if (is_array($params)) {
            foreach ($params as $param => $value) {
                switch ($param) {
                    case 'searchterm':
                        $qb->andWhere($qb->expr()->like('productLang.name', "'%$value%'"));
                        break;
                    case 'productId':
                        $qb->andWhere($qb->expr()->neq('product', (int)$value));
                        break;
                    default:
                        break;
                }
            }
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Category tree for multiple usage
     *
     * @param Entity\ProductEntity $product
     * @return array
     */
    public function getCategoryTreeForMultipleUsage(Entity\ProductEntity $product) {

        $categoryModel = new CategoryModel($this->_sl, $this->_logger);

        // prepare category items
        $items = array();
        foreach ($categoryModel->getFirstLevelProductCategories() as $category) {

            $items[] = $this->getCategoryDataForMultipleUsage($category, $product, 1);
        }

        return $items;
    }


    /**
     * Category data for multiple usage
     * @param Entity\CategoryEntity $category
     * @param Entity\ProductEntity $product
     * @return mixed
     */
    public function getCategoryDataForMultipleUsage(Entity\CategoryEntity $category, Entity\ProductEntity $product, $deep) {

        // entities
        $trans            = $category->getCurrentTranslation();
        $subCategories    = $category->getChilderen();
        $hasSubCategories = count($subCategories) > 0;
        $url              = $this->_sl->get('ViewHelperManager')->get('url');
        $result           = array();

        $isProductCategory = $category->getId() == $product->getProductCategories()->first()->getCategory()->getId();
        $isAssigned = $product->getMultipleUsages()->contains($category);

        // checkboxes
        $checkboxHelper = new \Zend\Form\View\Helper\FormCheckbox();
        $checkbox = new \Zend\Form\Element\Checkbox('assigned');
        $checkbox->setCheckedValue(1);
        $checkbox->setUncheckedValue(0);
        $checkbox->setValue($isAssigned);

        if ($isProductCategory) {
            $checkbox->setAttribute('disabled', 'disabled');
            $checkbox->setValue(true);
        }

        $dataHref = $url('home/default', array(
            'controller' => 'product',
            'action'     => 'multipleUsageAssignment',
            'param'      => 'product',
            'value'      => $product->getId(),
            'param2'     => 'category',
            'value2'     => $category->getId()
        ));
        $checkbox->setAttribute('data-href', $dataHref);

        // result
        $result['title']       = $trans->getName();
        $result['checkbox']    = $checkboxHelper->render($checkbox);
        $result['subitems']    = array();
        $result['hasSubitems'] = $hasSubCategories;
        if ($hasSubCategories) {
            foreach ($subCategories as $subCategory) {
                $result['subitems'][] = $this->getCategoryDataForMultipleUsage($subCategory, $product, $deep++);
            }
        }

        return $result;
    }
}