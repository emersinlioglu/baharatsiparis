<?php

namespace Ffb\Backend\Entity;

use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;

/**
 * TemplateEntity
 * This class extends the common FFB AbstractUserOwnedEntity class!
 *
 * @ORM\Entity
 * @ORM\Table(name="template")
 */
class TemplateEntity extends \Ffb\Common\Entity\AbstractBaseEntity {

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    protected $name;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="CategoryEntity", mappedBy="template", cascade={"persist", "remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $categories;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="TemplateAttributeGroupEntity", mappedBy="template", cascade={"persist", "remove"}, orphanRemoval=true, fetch="LAZY")
     * @ORM\OrderBy({"sort" = "ASC"})
     */
    protected $templateAttributeGroups;

    public function __construct() {

        $this->templateAttributeGroups = new Collections\ArrayCollection();
    }

    /**
     * Clone
     */
    public function __clone() {

        if ($this->templateAttributeGroups) {
            $templateAttributeGroups = new Collections\ArrayCollection();
            foreach($this->templateAttributeGroups as $templateAttributeGroup) {
                $templateAttributeGroup = clone $templateAttributeGroup;
                $templateAttributeGroup->setTemplate($this);
                $templateAttributeGroups->add($templateAttributeGroup);
            }
            $this->templateAttributeGroups = $templateAttributeGroups;
        } else {
            $this->templateAttributeGroups = new Collections\ArrayCollection();
        }

    }

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
    public function getType() {
        return $this->type;
    }

    /**
     * @return string
     */
    function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    function setName($name) {
        $this->name = $name;
    }

    /**
     * @return Collections\ArrayCollection
     */
    function getTemplateAttributeGroups() {
        return $this->templateAttributeGroups;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $templateAttributeGroups
     */
    function setTemplateAttributeGroups(\Doctrine\Common\Collections\ArrayCollection $templateAttributeGroups) {
        $this->templateAttributeGroups = $templateAttributeGroups;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $templateAttributeGroups
     */
    public function addTemplateAttributeGroups(\Doctrine\Common\Collections\Collection $templateAttributeGroups) {
        foreach ($templateAttributeGroups as $templateAttributeGroup) {
            $templateAttributeGroup->setTemplate($this);
            $this->templateAttributeGroups->add($templateAttributeGroup);
        }
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $templateAttributeGroups
     */
    public function removeTemplateAttributeGroups(\Doctrine\Common\Collections\Collection $templateAttributeGroups) {
        foreach ($templateAttributeGroups as $templateAttributeGroup) {
            $templateAttributeGroup->setTemplate(null);
            $this->templateAttributeGroups->removeElement($templateAttributeGroup);
        }
    }

}
