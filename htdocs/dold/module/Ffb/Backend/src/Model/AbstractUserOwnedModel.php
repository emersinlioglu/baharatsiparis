<?php

namespace Ffb\Backend\Model;

use Ffb\Backend\Entity;
use Ffb\Common\Entity as CommonEntity;
use Ffb\Backend\Service;


use Zend\ServiceManager;
use Zend\Log\Logger;

abstract class AbstractUserOwnedModel extends AbstractBaseModel {

    /**
     * @var Entity\UserEntity
     */
    protected $_identity;

    /**
     *
     * @param ServiceManager\ServiceLocatorInterface $sl
     * @param User $identity (optional) entity of user who uses this model
     */
    public function __construct(
        ServiceManager\ServiceLocatorInterface $sl,
        Entity\UserEntity $identity = null,
        Logger $logger = null
    ) {
        parent::__construct($sl, $identity);
        $this->_identity = $identity;
    }

    /**
     * Find location by ID.
     *
     * @param int $id
     * @return Ffb\Backend\Entity\AbstractBaseEntity|null
     *
     * @throws OptimisticLockException
     * @throws ORMInvalidArgumentException
     * @throws TransactionRequiredException
     * @throws ORMException
     */
    public function findById($id) {
        return is_null($id) ? null : $this->_em->find($this->_entityClass, $id);
    }

    /**
     * Insert data.
     *
     * In order to persist data that is given as array, its entity is read from
     * database, its values are amended and eventually stored.
     *
     * @param mixed $data
     * @param boolean $updateAuthor Toggles setting author
     * @return Entity\AbstractSequencedEntity $entity
     */
    public function insert($data) {

        if (is_array($data)) {

            $entity = new $this->_entityClass;
            $entity->exchangeArray($data);
        } else if (is_object($data)) {

            $entity = $data;
        }

        // update creator and modifier
        //if ($updateAuthor) {
        //    $this->_updateAuthor($entity);
        //}

        $this->_setUserId();

        // store entity
        parent::insert($entity, true);

        return $entity;
    }

    /**
     * Sets the userId in db session
     * so that the creator_user_id and modifier_user_id can be set
     * by the db triggers
     */
    protected function _setUserId() {

        if ($this->_identity !== null) {

            $userid = $this->_identity->getId();

            if ((int) $userid > 0) {
                $conn = $this->_em->getConnection();
                $conn->query('SET @userid = ' . $userid);
            }
        }
    }

    /**
     * Upate entity.
     *
     * @param mixed $data
     * @param $updateAuthor Toggles setting author
     * @return Entity\AbstractSequencedEntity
     * @throws \Exception if entity cannot be updated w/o ID
     */
    public function update($data, $updateAuthor = true) {

        if (is_array($data)) {

            if (!isset($data['id']) || empty($data['id'])) {
                throw new \Exception('entity (array) cannot be updated w/o ID');
            }
            $entity = $this->_em->find($this->_entityClass, $data['id']);
            $entity->exchangeArray($data);
        } else if (is_object($data)) {

            if (0 >= (int) $data->getId()) {
                throw new \Exception('entity (object) cannot be updated w/o ID');
            }
            $entity = $data;
        }

        /*
         * This part is moved in database triggers
         *
         * check for nested entities and update creator and modifier
         *
         * !!!! Because of performance reasons the author must be set manually !!!!
         * it takes really long time to set the author for nested entities automatically by this way
         */
        //if ($updateAuthor) {
        //    $this->_updateAuthor($entity);
        //}

        $this->_setUserId();

        // store entity
        parent::update($entity);

        return $entity;
    }

    /**
     * Get location files array for upload input
     *
     * @param int    $referenceId
     * @param string $referenceType
     * @param string $destination
     * @return array
     */
    public function getFilesForUploadInput($referenceId, $referenceType, $destination = null) {

        // get upload model
        $uploadModel = new UploadModel($this->_sl, $this->_identity);

        // get files
        $files       = $uploadModel->findByReference($referenceType, $referenceId, $destination);

        // result is empty
        $result      = array();

        if (count($files) === 0) {
            return $result;
        }

        /* @var $imageMngr \Ffb\Common\Image\ImageManager */
        $imageMngr = $this->_sl->get('Ffb\Common\Image\ImageManager');
        $conf = $this->_sl->get('Config');

        /* @var $uploadService \Ffb\Backend\Service\UploadService */
        $uploadService = $this->_sl->get('Ffb\Backend\Service\UploadService');

        /* @var $file \Ffb\Backend\Entity\UploadEntity */
        foreach ($files as $file) {

            // get image resolution
            $resolution = $uploadService->getImageSize($file->getTmpName());

            $confName = 'default';
            if (array_key_exists($referenceType, $conf['images'])) {
                $confName = $referenceType;
            }
            $imageMngr->setConfig($conf['images'][$confName]);

            // images,
            // TODO this area should been from config
            $imageView    = $imageMngr->getThumbnail($file->getTmpName(), 'uploadTablePreview', true);
            $imageGallery = $imageMngr->getThumbnail($file->getTmpName(), 'uploadGalleryPreview', true);

            // save file values
            $result[] = array(
                'id'          => $file->getId(),
                'name'        => $file->getName(),
                'rank'        => $file->getRank(),
                'size'        => $uploadService->sizeToString($file->getSize()),
                'description' => $file->getDescription(),
                'resolution'  => $resolution['width'] . 'x' . $resolution['height'],
                'url'         => $uploadService->parseFrontendUrl($file->getTmpName()),
                'urlView'     => $uploadService->parseFrontendUrl($imageView),
                'urlGallery'  => $uploadService->parseFrontendUrl($imageGallery)
            );
        }

        return $result;
    }

    /**
     *
     * @param \Ffb\Common\Entity\AbstractBaseEntity $entity
     * @param array $updatedEntites
     * @return array
     */
    protected function _updateAuthor(
        $entity,
        array $updatedEntites = array()
    ) {

        // do nothing if entity has already been updated
        // this is to avoid infinit loops!!!
        // if (in_array($entity, $updatedEntites)) return $updatedEntites;
        // Using in_array might result in "Nesting level too deep - recursive
        // dependency?", so check for identity instead!
        foreach ($updatedEntites as $ue) {
            if ($entity === $ue) {
                return $updatedEntites;
            }
        }

        if ($entity instanceof CommonEntity\AbstractUserOwnedEntity) {

            // assert identity for those entites that requires it
            if ($entity::REQUIRES_IDENTITY && is_null($this->_identity)) {
                throw new \Exception('cannot update author w/o identity');
            }

            $this->setAuthor($entity);
            }

            // remember this object as already updated to avoid infinit loops
            array_push($updatedEntites, $entity);

            // call this method recursivly for aggregated objects
            foreach ($entity->asArray() as $key => $property) {

                if (!is_object($property)) {

                    // skip non-object props
                    continue;
                } else if ($property instanceof CommonEntity\AbstractUserOwnedEntity) {
                    // update single child entity if is new
                    if (!$property->getCreatorId())  {
                        $updatedEntites = $this->_updateAuthor($property, $updatedEntites);
                    }
                } else if (
                    $property instanceof \Doctrine\ORM\PersistentCollection ||
                    $property instanceof \Doctrine\Common\Collections\ArrayCollection
                ) {
                    // update collection of child entities
                    foreach ($property as $item) {
                        if ($item instanceof CommonEntity\AbstractBaseEntity) {
                            $updatedEntites = $this->_updateAuthor($item, $updatedEntites);
                        }
                    }
                }
            }

        return $updatedEntites;
    }

    /**
     * Sets the creator, modifier, creation- and modification date
     *
     * @param CommonEntity\AbstractUserOwnedEntity $entity
     */
    public function setAuthor(CommonEntity\AbstractUserOwnedEntity $entity) {

        //set modifier user DATE
        $date = new \DateTime();
        $entity->setModifiedUserDate($date);
        //set creator user DATE
        if (is_null($entity->getCreatorUserId())) {
            $entity->setCreatedUserDate($date);
        }

        if ($this->_identity instanceof Entity\UserEntity) {
            $userId = (int) $this->_identity->getId();

            //set creator user ID
            if (is_null($entity->getCreatorUserId())) {
                $entity->setCreatorUserId($userId);
            }

            //set modifier user ID
            $entity->setModifierUserId($userId);
        }
    }
}