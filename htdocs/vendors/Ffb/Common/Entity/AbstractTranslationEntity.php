<?php

namespace Ffb\Common\Entity;

/**
 * AbstractTranslatableEntity
 *
 * This is the parent class for all entities which have a user as creator and
 * modifier and their respective dates as payload. It is the base class for most
 * of the "real" (i.e. not relational) entities of this application.
 */
abstract class AbstractTranslationEntity extends AbstractBaseEntity {

    /**
     * Temp property for empty Entities
     *
     * @var boolean
     */
    protected $isTemp = false;

    /**
     * Translations array
     *
     * @var \Ffb\Common\Entity\AbstractTranslatableEntity
     */
    protected $translationTarget;

    /**
     *
     * @return \Ffb\Common\Entity\AbstractTranslatableEntity
     */
    public function getTranslationTarget() {
        return $this->translationTarget;
    }

    /**
     *
     * @param \Ffb\Common\Entity\AbstractTranslatableEntity $entity
     * @return \Ffb\Backend\Entity\AbstractBaseEntity
     */
    public function setTranslationTarget($entity) {
        $this->translationTarget = $entity;
        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function getIsTemp() {
        return $this->isTemp;
    }

    /**
     *
     * @param boolean $isTemp
     * @return \Ffb\Common\Entity\AbstractTranslationEntity
     */
    public function setIsTemp($isTemp) {
        $this->isTemp = $isTemp;
        return $this;
    }
}
