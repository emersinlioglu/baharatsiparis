<?php

namespace Ffb\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CategoryLangLangEntity
 *
 * @ORM\Entity
 * @ORM\Table(name="category_lang")
 */
class CategoryLangEntity extends AbstractTranslationEntity {

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
     * @ORM\Column(name="alias", type="string", length=256, nullable=true)
     */
    protected $alias;

    /**
     * @var string
     * @ORM\Column(name="description", type="string", length=256, nullable=true)
     */
    protected $description;

    /**
     * @var CategoryLangEntity
     * @ORM\ManyToOne(targetEntity="CategoryEntity")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
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
    function getAlias() {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    function setAlias($alias) {
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     */
    function setDescription($description) {
        $this->description = $description;
    }

}