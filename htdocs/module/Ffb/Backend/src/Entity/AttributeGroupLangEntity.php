<?php

namespace Ffb\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AttributeGroupLangEntity
 *
 * @ORM\Entity
 * @ORM\Table(name="attribute_group_lang")
 */
class AttributeGroupLangEntity extends AbstractTranslationEntity {

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=256, nullable=true)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(name="title", type="string", length=256, nullable=true)
     */
    protected $title;

    /**
     * @var string
     * @ORM\Column(name="alias", type="string", length=255, nullable=false)
     */
    protected $alias;

    /**
     * @var LangEntity
     * @ORM\ManyToOne(targetEntity="LangEntity")
     * @ORM\JoinColumn(name="lang_id", referencedColumnName="id")
     */
    protected $lang;

    /**
     * @var AttributeGroupEntity
     * @ORM\ManyToOne(targetEntity="AttributeGroupEntity")
     * @ORM\JoinColumn(name="attribute_group_id", referencedColumnName="id")
     */
    protected $translationTarget;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    function getTitle() {
        return $this->title;
    }

    /**
     * @param string $title
     */
    function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getAlias() {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias($alias) {
        $this->alias = $alias;
    }

}