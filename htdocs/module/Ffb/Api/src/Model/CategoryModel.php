<?php

namespace Ffb\Api\Model;

use \Ffb\Backend\Entity;
use \Zend\ServiceManager;
use \Zend\Log\Logger;

/**
 * @author erdal.mersinlioglu
 */
class CategoryModel extends \Ffb\Api\Model\AbstractBaseModel {

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param \Zend\Log\Logger $logger
     * @param Entity\UserEntity $identity
     */
    public function __construct(
            ServiceManager\ServiceLocatorInterface $sl,
            Logger $logger = null,
            Entity\UserEntity $identity = null) {
        $this->_entityClass = 'Ffb\Backend\Entity\CategoryEntity';
        parent::__construct($sl, $logger, $identity);
    }

    /**
     * @param $language
     * @param $categoryId
     * @return array
     */
    public function getCategoriesData($language, $categoryId) {

        $qb = $this->getRepository()->createQueryBuilder('attribute');
        $qb->leftJoin(
            'attribute.translations',
            'attributeLang',
            \Doctrine\ORM\Query\Expr\Join::WITH ,
            'attributeLang.lang = ' . Entity\LangEntity::DEFAULT_LANGUAGE_ID
        );
        $qb->orderBy('attributeLang.name');

        $qb = $this->getRepository()->createQueryBuilder('category');
        $qb->orderBy('category.sort', 'ASC');

        if ($categoryId) {
            $qb->andWhere($qb->expr()->eq('category.parent', $categoryId));
        } else {
            // root categories
            $qb->andWhere($qb->expr()->isNull('category.parent'));
        }

        $result = array(
            'categories' => array()
        );
        foreach ($qb->getQuery()->getResult() as $category) {
            $result['categories'][] = $this->getCategoryData($category, $language);
        }

        return $result;
    }

    /**
     * @param Entity\CategoryEntity $category
     * @param $language
     * @return array
     */
    public function getCategoryData(Entity\CategoryEntity $category, $language) {

        $name = $category->getCurrentTranslation($language)->getName();
        if (!$name) {
            $name = $category->getCurrentTranslation($this->getMasterLanguage())->getName();
        }

        $result = array(
            'id'    => $category->getId(),
            'name'  => $name,
            'categories' => array()
        );

        foreach ($category->getChilderen() as $subCategory) {
            $result['categories'][] = $this->getCategoryData($subCategory, $language);
        }

        return $result;
    }
}