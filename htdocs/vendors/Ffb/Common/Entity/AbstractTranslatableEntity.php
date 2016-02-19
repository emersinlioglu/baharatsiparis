<?php

namespace Ffb\Common\Entity;

use \Doctrine\Common\Collections;

/**
 * AbstractTranslatableEntity
 *
 * This is the parent class for all entities which have a user as creator and
 * modifier and their respective dates as payload. It is the base class for most
 * of the "real" (i.e. not relational) entities of this application.
 */
abstract class AbstractTranslatableEntity extends AbstractUserOwnedEntity {

    /**
     * Translations array
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     * Example: @ORM\OneToMany(targetEntity="[TranslationsEntity]", mappedBy="translationTarget", cascade={"persist", "remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $translations;

    /**
     *
     * @var string
     */
    protected $_translationEntity;

    /**
     * Construct
     *
     */
    public function __construct() {
        parent::__construct();

        $this->translations = new Collections\ArrayCollection();
    }

    /**
     * Clone
     *
     */
    public function __clone() {
        //parent::__clone();

        $this->translations = new Collections\ArrayCollection();
    }

    /**
     *
     * @return Collections\ArrayCollection $translations
     */
    public function getTranslations() {
        return $this->translations;
    }

    /**
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $translations
     * @return \Ffb\Common\Entity\AbstractTranslatableEntity
     */
    public function setTranslations($translations) {
        $this->translations = $translations;
        return $this;
    }

    /**
     *
     * @param Collections\ArrayCollection $translations
     */
    public function addTranslations(Collections\ArrayCollection $translations) {
        foreach ($translations as $translation) {
            $translation->setTranslationTarget($this);
            $this->translations->add($translation);
        }
    }

    /**
     *
     * @param Collections\ArrayCollection $translations
     */
    public function removeTranslations(Collections\ArrayCollection $translations) {
        foreach ($translations as $translation) {
            $translation->setTranslationTarget(null);
            $this->translations->removeElement($translation);
        }
    }

    /**
     * @param string $locale
     * @return translation
     */
    public function getCurrentTranslation($locale = null) {

        // DEREVK-622 return translate-key for debugging
        if (
                isset($_SERVER, $_SERVER['HTTP_USER_AGENT'])
             && $_SERVER['HTTP_USER_AGENT'] == /*$this->config['module']['translator'][*/'translation_debugger'//]
             || isset($_COOKIE, $_COOKIE['TRANSLATION_DEBUGGER'])
             && $_COOKIE['TRANSLATION_DEBUGGER'] == 1
        ) {
            $locale = 'translation_debugger';
        }

        // get current lang form session
        if (is_null($locale)) {
            $session = new \Zend\Session\Container('default');
            $locale  = $session->offsetGet('languageCode');

            if (is_null($locale)) {
                $locale = 'de';
                error_log('could not find lang in session .. assume "de"');
            }
        }

        foreach ($this->getTranslations() as $translation) {
            if (strtolower($locale) === strtolower($translation->getLanguageCode())) {
                return $translation;
            }
        }

        if ($this->_translationEntity) {
            $tempEntity = new $this->_translationEntity;
            $tempEntity->setIsTemp(true);
            return $tempEntity;
        }

        return null;
    }

    /**
     * Clone translations
     */
    public function cloneTranslations() {

        if ($this->translations) {

            $translations = new Collections\ArrayCollection();

            /* @var $translation \Ffb\Common\Entity\AbstractTranslationEntity */
            foreach ($this->translations as $translation) {
                $translation = clone $translation;
                $translation->setTranslationTarget($this);
                $translations->add($translation);
            }
            $this->translations = $translations;
        } else {
            $this->translations = new Collections\ArrayCollection();
        }
    }
}
