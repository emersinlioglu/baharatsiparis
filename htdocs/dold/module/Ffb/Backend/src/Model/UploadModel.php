<?php

namespace Ffb\Backend\Model;

use Ffb\Backend\Entity;

use Zend\ServiceManager;

/**
 *
 * @author
 */
class UploadModel extends AbstractBaseModel {

//    /**
//     *
//     * @param ServiceManager\ServiceLocatorInterface $sl
//     * @param User $identity (optional) entity of user who uses this model
//     */
//    public function __construct(ServiceManager\ServiceLocatorInterface $sl, Entity\UserEntity $identity = null) {
//        $this->_entityClass = 'Ffb\Backend\Entity\UploadEntity';
//        parent::__construct($sl, $identity);
//    }
    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param \Zend\Log\Logger $logger
     * @param Entity\UserEntity $identity
     */
    public function __construct(
            ServiceManager\ServiceLocatorInterface $sl,
            \Zend\Log\Logger $logger = null,
            Entity\UserEntity $identity = null) {
        $this->_entityClass = 'Ffb\Backend\Entity\UploadEntity';
        parent::__construct($sl, $logger, $identity);
    }


    /**
     * Will return count of uploads for reference
     *
     * @param string $referenceId
     * @param int $referenceType
     * @param string $destination
     * @return array
     */
    public function countByReference($referenceId, $referenceType, $destination = null) {

        if (!is_numeric($referenceId)) {
            return array();
        }

        $criteries = array(
            'referenceType' => $referenceType,
            'referenceId' => $referenceId
        );

        if ($destination) {
            $criteries['destination'] = $destination;
        }
        $count = count($this->getRepository()->findBy($criteries));
        if (empty($count)) {
            $count = 0;
        }

        return $count;
    }

    /**
     * Will return empty array if given $referenceId is null.
     *
     * @param string $referenceType
     * @param int $referenceId
     * @param string $destination
     * @param int $limit
     * @return array
     */
    public function findByReference($referenceType, $referenceId, $destination = null, $limit = null) {

        if (!is_numeric($referenceId)) {
            return array();
        }

        $criteries = array(
            'referenceType' => $referenceType,
            'referenceId' => $referenceId
        );

        if ($destination) {
            $criteries['destination'] = $destination;
        }

        $order = array(
            'rank' => 'DESC'
        );

        return $this->getRepository()->findBy($criteries, $order, $limit);
    }

    /**
     * returns start image of reference
     * rank equals 0 -> start image
     */
    public function findStartImage($referenceType, $referenceId){

        if (!is_numeric($referenceId)) {
            return array();
        }

        $criteries = array(
            'referenceType' => $referenceType,
            'referenceId' => $referenceId,
            'destination' => 'images',
            'rank' => 1
        );
        return $this->getRepository()->findBy($criteries, null, 1);
    }

    /**
     * Set rank by reference Id, type and destination
     *
     * @param int $rank
     * @param string $referenceType
     * @param int $referenceId
     * @param string $destination
     */
    public function setRankByCriteries($rank, $referenceType, $referenceId, $destination = null) {

        if (!is_numeric($referenceId)) {
            return false;
        }

        $query = $this->getRepository()->createQueryBuilder('e')
            ->update()
            ->set('e.rank', (int) $rank)
            ->andWhere('e.referenceType = :referenceType')
            ->andWhere('e.referenceId = :referenceId')
            ->setParameters(array(
                 'referenceType' => $referenceType,
                 'referenceId' => $referenceId
            ));

        if ($destination) {
            $query->andWhere('e.destination = :destination')
                ->setParameter('destination', $destination);
        }

        $result = $query->getQuery()->getResult();
        return $result;
    }
}