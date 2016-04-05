<?php

namespace Ffb\Backend\Entity;

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;

/**
 * TemplateAttributeGroupEntity
 *
 * @ORM\Entity
 * @ORM\Table(name="template_attribute_group")
 */
class TemplateAttributeGroupEntity extends \Ffb\Common\Entity\AbstractBaseEntity {

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(name="sort", type="integer", nullable=false, options={"unsigned":true, "default":0})
     */
    protected $sort;

    /**
     * @var TemplateEntity
     * @ORM\ManyToOne(targetEntity="TemplateEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;

    /**
     * @var AttributeGroupEntity
     * @ORM\ManyToOne(targetEntity="AttributeGroupEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="attribute_group_id", referencedColumnName="id")
     */
    protected $attributeGroup;

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
     * @return int
     */
    public function getSort() {
        return $this->sort;
    }

    /**
     * @param int $sort
     */
    public function setSort($sort) {
        $this->sort = $sort;
    }

    /**
     * @return TemplateEntity
     */
    function getTemplate() {
        return $this->template;
    }

    /**
     * @param \Ffb\Backend\Entity\TemplateEntity $template
     */
    function setTemplate(TemplateEntity $template = null) {
        $this->template = $template;
    }

    /**
     * @return AttributeGroupEntity
     */
    function getAttributeGroup() {
        return $this->attributeGroup;
    }

    /**
     * @param \Ffb\Backend\Entity\AttributeGroupEntity $attributeGroup
     */
    function setAttributeGroup(AttributeGroupEntity $attributeGroup) {
        $this->attributeGroup = $attributeGroup;
    }
}
