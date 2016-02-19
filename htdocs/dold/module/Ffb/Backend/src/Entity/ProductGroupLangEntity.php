<?php

namespace Ffb\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductGroupLangEntity
 *
 * @ORM\Entity
 * @ORM\Table(name="product_group_lang")
 */
class ProductGroupLangEntity extends AbstractTranslationEntity {

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
     * @var ProductGroupEntity
     * @ORM\ManyToOne(targetEntity="ProductGroupEntity")
     * @ORM\JoinColumn(name="product_group_id", referencedColumnName="id")
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

}