<?php

namespace Ffb\Backend\Model;

use Ffb\Backend\Entity;

use Zend\ServiceManager;

/**
 * @author erdal.mersinlioglu
 */
class AttributeModel extends AbstractBaseModel {

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param \Zend\Log\Logger $logger
     * @param Entity\UserEntity $identity
     */
    public function __construct(
            ServiceManager\ServiceLocatorInterface $sl,
            \Zend\Log\Logger $logger = null,
            Entity\UserEntity $identity = null) {
        $this->_entityClass = 'Ffb\Backend\Entity\AttributeEntity';
        parent::__construct($sl, $logger, $identity);
    }

    /**
     * Sorts the attributes by name in default language
     * @return array
     */
    public function findAll() {

        $qb = $this->getRepository()->createQueryBuilder('attribute');
        $qb->leftJoin(
            'attribute.translations',
            'attributeLang',
            \Doctrine\ORM\Query\Expr\Join::WITH ,
            'attributeLang.lang = ' . Entity\LangEntity::DEFAULT_LANGUAGE_ID
        );
        $qb->orderBy('attributeLang.name');

        return $qb->getQuery()->getResult();
    }

    /**
     * Finds attributes for the subnavi
     *
     * @param array $params
     * @return array
     */
    public function findForSubnavi(array $params) {

        $qb = $this->getRepository()->createQueryBuilder('attribute');
        $qb->leftJoin('attribute.translations', 'attributeLang', \Doctrine\ORM\Query\Expr\Join::WITH, 'attributeLang.lang = ' . Entity\LangEntity::DEFAULT_LANGUAGE_ID);

        foreach ($params as $attr => $value) {
            switch ($attr) {
                //case 'attributeGroupAttributes':
                //    $qb->leftJoin('attribute.attributeGroupAttributes', 'attributeGroupAttributes');
                //    $qb->leftJoin('attributeGroupAttributes.attributeGroup', 'attributeGroup');
                //    if (!is_array($value)) {
                //        $value = array($value);
                //    }
                //    $qb->andWhere($qb->expr()->in('attributeGroup.id', $value));
                //    break;
                case 'search':
                    $qb->andWhere($qb->expr()->like('attributeLang.name', ':searchterm'));
                    $qb->setParameter('searchterm', "%$value%");
                    break;

                default:
                    break;
            }
        }

        // order by
        $qb->orderBy('attributeLang.name');

        return $qb->getQuery()->getResult();
    }

    /**
     * Return attribute type value options
     *
     * @return array
     */
    public function getAttributeTypeValueOptions() {

        return array(
            Entity\AttributeEntity::TYPE_VARCHAR        => 'LBL_ATTRIBUTE_TYPE_VARCHAR',
            Entity\AttributeEntity::TYPE_TEXT           => 'LBL_ATTRIBUTE_TYPE_TEXT',
            Entity\AttributeEntity::TYPE_INT            => 'LBL_ATTRIBUTE_TYPE_INT',
            Entity\AttributeEntity::TYPE_FLOAT          => 'LBL_ATTRIBUTE_TYPE_FLOAT',
            Entity\AttributeEntity::TYPE_BOOL           => 'LBL_ATTRIBUTE_TYPE_BOOL',
            Entity\AttributeEntity::TYPE_RANGE_INT      => 'LBL_ATTRIBUTE_TYPE_RANGE_INT',
            Entity\AttributeEntity::TYPE_RANGE_FLOAT    => 'LBL_ATTRIBUTE_TYPE_RANGE_FLOAT',
            Entity\AttributeEntity::TYPE_IMAGE          => 'LBL_ATTRIBUTE_TYPE_IMAGE',
            Entity\AttributeEntity::TYPE_DOCUMENT       => 'LBL_ATTRIBUTE_TYPE_DOCUMENT',
            Entity\AttributeEntity::TYPE_SELECT         => 'LBL_ATTRIBUTE_TYPE_SELECT'
        );
    }
}