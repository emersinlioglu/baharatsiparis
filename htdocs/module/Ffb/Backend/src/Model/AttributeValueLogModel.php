<?php

namespace Ffb\Backend\Model;

use Ffb\Backend\Entity;

use Doctrine\ORM\Query\Expr;
use Zend\ServiceManager;

/**
 * @author erdal.mersinlioglu
 */
class AttributeValueLogModel extends AbstractBaseModel {

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param \Zend\Log\Logger $logger
     * @param Entity\UserEntity $identity
     */
    public function __construct(
            ServiceManager\ServiceLocatorInterface $sl,
            \Zend\Log\Logger $logger = null,
            Entity\UserEntity $identity = null) {
        $this->_entityClass = 'Ffb\Backend\Entity\AttributeValueLogEntity';
        parent::__construct($sl, $logger, $identity);
    }

    /**
     * Get paged data
     *
     * @param int $offset
     * @param int $limit
     * @param string $sortBy
     * @param string $sortDir
     * @param array $filters
     * @return array
     */
    public function getPageData(
        $offset  = 0,
        $limit   = 20,
        $sortBy  = 'date',
        $sortDir = 'desc',
        $search  = null,
        $filters = array()
    ) {

        $qb = $this->getRepository()->createQueryBuilder('avLog');

        // joins
        $qb->leftJoin('avLog.attributeValue', 'attributeValue', Expr\Join::WITH);
        $qb->leftJoin('attributeValue.attributeLang', 'attributeLang', Expr\Join::WITH);
        $qb->leftJoin('attributeLang.translationTarget', 'attribute', Expr\Join::WITH);
        $qb->leftJoin('attributeValue.productLang', 'productLang', Expr\Join::WITH);
        $qb->leftJoin('productLang.translationTarget', 'product', Expr\Join::WITH);

        // sort
        if ($sortBy) {
            $qb->orderBy('avLog.' . $sortBy, $sortDir);
        } else {
            $qb->orderBy('avLog.date', 'desc');
        }

        // limit
        if ($offset !== null && $limit !== null) {
            $qb->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        $qb->andWhere($qb->expr()->notIn('attribute.type', ':notLoggedAttributeTypes'))
            ->setParameter('notLoggedAttributeTypes', array(
                Entity\AttributeEntity::TYPE_IMAGE,
                Entity\AttributeEntity::TYPE_DOCUMENT
            ));

        foreach($filters as $key => $value) {
            switch ($key) {
                case 'productId':
                    $qb->andWhere('product.id = :productId');
                    $qb->setParameter('productId', $value);
                    break;
                case 'timeperiod':
                    $today = new \DateTime();
                    $today->setTime(0, 0, 0);
                    switch ($value) {
                        case 'lastDay':
                            $timeUnit = 'day';
                            break;
                        case 'lastWeek':
                            $timeUnit = 'week';
                            break;
                        case 'lastYear':
                            $timeUnit = 'year';
                            break;
                        default:
                            $timeUnit = 'day';
                            break;
                    };
                    $from = $today->modify("-1 $timeUnit");
                    $qb->andWhere($qb->expr()->gte('avLog.date', ':from'))
                        ->setParameter('from', $from);
                    break;
                default:
                    break;
            };
        }

        return $qb->getQuery()->getResult();
    }

}