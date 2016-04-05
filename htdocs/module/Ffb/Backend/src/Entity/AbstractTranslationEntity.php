<?php

namespace Ffb\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AbstractTranslatableEntity
 *
 * This is the parent class for all entities which have a user as creator and
 * modifier and their respective dates as payload. It is the base class for most
 * of the "real" (i.e. not relational) entities of this application.
 */
abstract class AbstractTranslationEntity extends \Ffb\Common\Entity\AbstractBaseEntity {

    /**
     * Temp property for empty Entities
     *
     * @var boolean
     */
    protected $isTemp = false;

    /**
     * @var LangEntity
     * @ORM\ManyToOne(targetEntity="LangEntity")
     * @ORM\JoinColumn(name="lang_id", referencedColumnName="id")
     */
    protected $lang;

    /**
     * Translations array
     *
     * @var \Ffb\Backend\Entity\AbstractTranslatableEntity
     */
    protected $translationTarget;

    /**
     * @return LangEntity
     */
    function getLang() {
        return $this->lang;
    }

    /**
     * @param \Ffb\Backend\Entity\LangEntity $lang
     */
    function setLang(LangEntity $lang) {
        $this->lang = $lang;
    }

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
