<?php

namespace Ffb\Backend\Model;

use Doctrine\ORM\Query\Expr\Join;
use Ffb\Backend\Entity;
use \Ffb\Common\I18n\Translator\Translator;
use \Ffb\Backend\View\Helper;

use Zend\ServiceManager;
use Doctrine\Common\Collections;

/**
 * @author erdal.mersinlioglu
 */
class ProductLangModel extends AbstractBaseModel {

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
        $this->_entityClass = 'Ffb\Backend\Entity\ProductLangEntity';
        parent::__construct($sl, $logger, $identity);

        $moduleConf = $this->_sl->get('Config');
        // masterLang
        $this->_masterLang = $moduleConf['translator']['master_language_code'];
    }

    /**
     * Get products
     *
     * @param array $data
     * @return array
     */
    public function getProducts(array $data = array()) {
        $qb = $this->getRepository()->createQueryBuilder('productLang');

        $qb->distinct(true);
        $qb->leftJoin('productLang.lang', 'lang');
        $qb->leftJoin('productLang.translationTarget', 'product');
        $qb->leftJoin('product.productCategories', 'productCategory');
        $qb->leftJoin('productCategory.category', 'category');
        $qb->leftJoin('category.translations', 'categoryLang');
        $qb->leftJoin('categoryLang.lang', 'categoryLangLang');

        foreach ($data as $key => $value) {
            switch($key) {
                case 'lang':
                    $qb->andWhere('lang.iso = :lang');
                    $qb->andWhere('categoryLangLang.iso = :lang');
                    $qb->setParameter('lang', $value);
                    break;
            }
        }

        $qb->addOrderBy('categoryLang.sort', 'ASC');
        $qb->addOrderBy('productLang.name', 'ASC');
//        echo $qb->getQuery()->getSQL();
        return $qb->getQuery()->getResult();
    }

    public function groupProducts(array $productLangs = array()) {

        $data = array();
        foreach ($productLangs as $productLang) {
            $langId = $productLang->getLang()->getId();
            $product = $productLang->getTranslationTarget();
            $category = $product->getProductCategories()->first()->getCategory();
            $categoryName = $category->getCurrentTranslation($langId)->getName();

//            $data[$category->getId()]['name'] = $categoryName;
//            $data[$category->getId()]['products'][] = $productLang;
            $data[] = array(
                'categoryName' => $categoryName,
                'categoryId' => $category->getId(),
                'productName' => $productLang->getName(),
                'productId' => $product->getId(),
                'productPrice' => $product->getPrice(),
                'productAmount' => $product->getAmount(),
                'productImageUrl' => $product->getImageUrl(),
            );
        }

        return $data;
    }
}