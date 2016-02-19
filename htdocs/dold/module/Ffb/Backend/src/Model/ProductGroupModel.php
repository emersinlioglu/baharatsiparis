<?php

namespace Ffb\Backend\Model;

use Ffb\Backend\Entity;

use Zend\ServiceManager;

/**
 * @author erdal.mersinlioglu
 */
class ProductGroupModel extends AbstractBaseModel {

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param \Zend\Log\Logger $logger
     * @param Entity\UserEntity $identity
     */
    public function __construct(
            ServiceManager\ServiceLocatorInterface $sl,
            \Zend\Log\Logger $logger = null,
            Entity\UserEntity $identity = null) {
        $this->_entityClass = 'Ffb\Backend\Entity\ProductGroupEntity';
        parent::__construct($sl, $logger, $identity);
    }

    /**
     * Returns the sub product groups of the $productGroup
     *
     * @param \Ffb\Backend\Entity\ProductGroupEntity $productGroup
     * @return array
     */
    public function getFirstLevelProductGroups() {

        $qb = $this->getRepository()->createQueryBuilder('productGroup');
        $qb->andWhere($qb->expr()->isNull('productGroup.parent'));

        return $qb->getQuery()->getResult();
    }

    /**
     * Finds the root product groups to assign as parent
     *
     * @return array (
     *      'id',
     *      'productGroupName',
     *      'parentId'
     * )
     */
    public function findFirstAndSecondLevelParentGroups() {
        $qb = $this->getRepository()->createQueryBuilder('productGroup');

        $qb->select('productGroup.id as id');
        $qb->addSelect('childeren.id as parentId');
        $qb->leftJoin('productGroup.childeren', 'childeren');
        $qb->andWhere('productGroup.parent IS NULL');

//        $qb->addSelect('trans.name as productGroupName');
//        $qb->leftJoin('productGroup.translations', 'trans');
//        $qb->leftJoin('trans.lang', 'lang');
//        $qb->andWhere('lang.iso = :iso');

//        $qb->leftJoin('childeren.translations', 'childerenTrans');
//        $qb->leftJoin('childerenTrans.lang', 'childerenLang');
//        $qb->andWhere('childerenLang.iso = :iso');
//        $qb->setParameters(array(
//            'iso' => Entity\LangEntity::DEFAULT_LANGUAGE_CODE
//        ));

        $result = array();
        $resultHierarchic = array();
        foreach ($qb->getQuery()->getResult() as $row) {
            $firstLevel = $row['id'];
            $secondLevel = $row['parentId'];
            $resultHierarchic[$firstLevel][] = $secondLevel;

            if (!empty($firstLevel)) {
                $result[$firstLevel] = $firstLevel;
            }
            if (!empty($secondLevel)) {
                $result[$secondLevel] = $secondLevel;
            }
        }

        return array(
            'hierarchic' => $resultHierarchic,
            'all' => $result
        );
    }

    public function getProductGroupNames(array $ids) {

        $qb = $this->getRepository()->createQueryBuilder('productGroup');

        $qb->select('productGroup.id');
        $qb->addSelect('trans.name');
        $qb->leftJoin('productGroup.translations', 'trans');
        $qb->leftJoin('trans.lang', 'lang');
        $qb->andWhere('productGroup in (:ids)');
        $qb->andWhere('lang.iso = :iso');
        $qb->setParameter('ids', $ids);
        $qb->setParameter('iso', Entity\LangEntity::DEFAULT_LANGUAGE_CODE);

        $result = array();
        foreach ($qb->getQuery()->getResult() as $row) {
            $result[$row['id']] = $row['name'];
        }

        return $result;
    }

    public function getParentsValueOptions() {

        $pgs = $this->findFirstAndSecondLevelParentGroups();
        $pgHierarchic = $pgs['hierarchic'];
        $pgNames = $this->getProductGroupNames($pgs['all']);

        $result = array();
        foreach ($pgHierarchic as $firstLevelPgId => $secondLevelPgs) {

            $options = array();
            $options[] = array(
                'label' => $pgNames[$firstLevelPgId],
                'value' => $firstLevelPgId,
                'attributes' => array('class' => 'first-level')
            );
            foreach ($secondLevelPgs as $secondLevelPgId) {
                if (empty($secondLevelPgId)) {
                    continue;
                }
                $options[] = array(
                    'label' => $pgNames[$secondLevelPgId],
                    'value' => $secondLevelPgId,
                    'attributes' => array('class' => 'second-level')
                );
            }

            $result = array_merge($result, $options);
        }

        return $result;
    }

}