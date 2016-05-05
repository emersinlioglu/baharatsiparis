<?php

namespace Ffb\Backend\Model;

use Ffb\Backend\Entity;

use Zend\ServiceManager;

/**
 *
 * @author erdal.mersinlioglu
 */
class CategoryLangModel extends AbstractBaseModel {

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param \Zend\Log\Logger $logger
     * @param Entity\UserEntity $identity
     */
    public function __construct(
            ServiceManager\ServiceLocatorInterface $sl,
            \Zend\Log\Logger $logger = null,
            Entity\UserEntity $identity = null) {
        $this->_entityClass = 'Ffb\Backend\Entity\CategoryLangEntity';
        parent::__construct($sl, $logger, $identity);
    }

    public function getSortedByCategory() {
        $qb = $this->getRepository()->createQueryBuilder('categoryLang');

        $langModel = new LangModel($this->_sl, $this->_logger);
        $activeLanguages = $langModel->getActiveLanguages();

        $qb->select(array(
           'categoryLang.id',
           'categoryLang.sort',
           'categoryLang.name',
           'lang.iso',
        ));
        $qb->leftJoin('categoryLang.translationTarget', 'category');
        $qb->leftJoin('categoryLang.lang', 'lang');
        $qb->leftJoin('category.parent', 'categoryParent');

//        $qb->andWhere($qb->expr()->eq('category', ':category'));
//        $qb->setParameter('category', $category);
//
//        $qb->andWhere($qb->expr()->eq('lang', ':lang'));
//        $qb->setParameter('lang', $lang);

        $qb->andWhere($qb->expr()->isNull('categoryParent'));
        $qb->andWhere('lang in (:langs)');
        $qb->setParameter('langs', $activeLanguages);

        $qb->orderBy('lang.iso', 'asc');
        $qb->addOrderBy('categoryLang.sort', 'asc');

//        error_log($qb->getQuery()->getSQL());
        return $qb->getQuery()->getResult();
    }

    /**
     * Categories for frontend
     *
     * @param array $data
     * @return array
     */
    public function getCategoriesForFrontend(array $data = array()) {
        $qb = $this->getRepository()->createQueryBuilder('categoryLang');

        $langModel = new LangModel($this->_sl, $this->_logger);
        $activeLanguages = $langModel->getActiveLanguages();

        $qb->select(array(
            'categoryLang.id',
            'categoryLang.sort',
            'categoryLang.name',
            'lang.iso',
        ));
        $qb->leftJoin('categoryLang.translationTarget', 'category');
        $qb->leftJoin('categoryLang.lang', 'lang');
        $qb->leftJoin('category.parent', 'categoryParent');

//        $qb->andWhere($qb->expr()->eq('category', ':category'));
//        $qb->setParameter('category', $category);
//
//        $qb->andWhere($qb->expr()->eq('lang', ':lang'));
//        $qb->setParameter('lang', $lang);

        $qb->andWhere($qb->expr()->isNull('categoryParent'));
        $qb->andWhere('lang in (:langs)');
        $qb->setParameter('langs', $activeLanguages);

        $qb->orderBy('lang.iso', 'asc');
        $qb->addOrderBy('categoryLang.sort', 'asc');

        return $qb->getQuery()->getResult();
    }

}