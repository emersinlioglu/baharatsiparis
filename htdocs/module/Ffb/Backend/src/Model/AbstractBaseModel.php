<?php

namespace Ffb\Backend\Model;

use Ffb\Backend\Entity;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractBaseModel implements FactoryInterface {

    /**
     * Service Locator
     *
     * @var ServiceLocatorInterface
     */
    protected $_sl;

    /**
     * Entity manager
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_em;

    /**
     * Logger
     *
     * @var \Zend\Log\Logger
     */
    protected $_logger;

    /**
     * Identity
     *
     * @var Entity\UserEntity
     */
    protected $_identity;

    /**
     *
     * @var string
     */
    protected $_entityClass;

    /**
     * @param ServiceLocatorInterface $sl
     * @param \Zend\Log\Logger $logger
     * @param Entity\UserEntity $identity
     */
    public function __construct(ServiceLocatorInterface $sl, \Zend\Log\Logger $logger = null, Entity\UserEntity $identity = null) {
        $this->_sl = $sl;
        $this->_em = $this->_sl->get('Doctrine\ORM\EntityManager');
        $this->_logger = $logger;
        $this->_identity = $identity;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $sl
     */
    public function createService(ServiceLocatorInterface $sl) {
        $this->_sl = $sl;
    }

    /**
     * Insert data in db
     *
     * @param mixed $entry
     */
    public function insert($entry) {

        // set user id
        $this->_setUserId();

        if (is_array($entry)) {

            $entity = new $this->_entityClass;
            $entity->exchangeArray($entry);
            $this->insertEntity($entry);
        } else if (is_object($entry)) {

            $this->insertEntity($entry);
        } else {
            throw new \Exception('wrong entry type');
        }
    }

    /**
     * Insert entity in db
     *
     * @param mixed $entity
     */
    public function insertEntity($entity) {

        // set user id
        $this->_setUserId();

        $this->_em->persist($entity);
            $this->_em->flush();
        }

    /**
     * Update data in db
     *
     * @param mixed $entry
     */
    public function update($entry) {

        // set user id
        $this->_setUserId();

        if (is_array($entry)) {

            $entity = new $this->_entityClass;
            $entity->exchangeArray($entry);
            $this->updateEntity($entry);
        } else if (is_object($entry)) {

            $this->updateEntity($entry);
        } else {
            throw new \Exception('wrong entry type');
        }
    }

    /**
     * Insert entity in db
     *
     * @param mixed $entity
     */
    public function updateEntity($entity) {

        // set user id
        $this->_setUserId();
        
        $this->_em->persist($entity);
            $this->_em->flush();
        }

    /**
     * Delete entry from db
     *
     * @param mixed $entry
     */
    public function delete($entry) {
        if (is_object($entry)) {
             $this->deleteEntity($entry);
        }
    }

    /**
     * Delete Entity
     *
     * @param object $entity
     */
    public function deleteEntity($entity) {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    /**
     * Deletes multiple entities, contrary to deleteEntity it flushes the changes
     * after deleting the last entry
     *
     * @param object[] $entities
     */
    public function deleteEntities(array $entities) {
        foreach ($entities as $entity) {
            if (is_object($entity)) {
                $this->_em->remove($entity);
            }
        }
        $this->_em->flush();
    }

    /**
     * Get repository for entities of current model.
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository() {
        return $this->_em->getRepository($this->_entityClass);
    }

    /**
     * Get all locations.
     *
     * @return array:AbstractBaseEntity
     */
    public function findAll() {
        return $this->getRepository()->findAll();
    }

    /**
     * Finds entities by a set of criteria.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null) {
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Find entity by ID.
     *
     * @param int $id
     * @return Ffb\Tms\Entity\AbstractBaseEntity|null
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
     * @param array $criteria
     * @return object|null The entity instance or NULL if the entity can not be found.
     */
    public function findOneBy(array $criteria) {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * @see http://stackoverflow.com/questions/11586961/doctrine-2-getsql-from-em-find
     */
    public function enableLogging(\Zend\Log\Logger $logger = null) {
        if (is_null($logger)) {
            $el = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        } else {
            $this->_logger = $logger;
            $el = new \Ffb\Backend\Model\Logger\AppSQLLogger($logger);
        }
        $this->_em->getConfiguration()->setSQLLogger($el);
    }

    /**
     *
     */
    public function getEntityManager() {
        return $this->_em;
    }

    /**
     * Creates missing translations
     *
     * @param Entity\AbstractTranslatableEntity $entity
     * @param string $langEntityName
     * @return boolean
     */
    public function createMissingTranslations($entity, $langEntityName) {

        if (!$entity instanceof \Ffb\Backend\Entity\AbstractTranslatableEntity) {
            return false;
        }

        $langModel     = new LangModel($this->_sl);

        $existingLangIds = array();
        foreach ($entity->getTranslations() as $trans) {
            $existingLangIds[] = $trans->getLang()->getId();
        }

        // create missing languages
        $translations = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($langModel->getActiveLanguages() as $lang) {

            if (!in_array($lang->getId(), $existingLangIds)) {

                $langEntity = new $langEntityName();
                $langEntity->setLang($lang);
                $translations->add($langEntity);
            }
        }
        $entity->addTranslations($translations);

        return true;
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
}