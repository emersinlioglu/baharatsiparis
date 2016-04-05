<?php

namespace Ffb\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Upload
 *
 * @ORM\Entity
 * @ORM\Table(name="upload")
 */
class UploadEntity extends \Ffb\Common\Entity\AbstractBaseEntity {

    /**
     * id INT UNSIGNED NOT NULL
     *
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * reference_type VARCHAR(256) NOT NULL
     *
     * @var string
     * @ORM\Column(name="reference_type", type="string", length=256)
     */
    protected $referenceType;

    /**
     * reference_id INT(10) UNSIGNED NOT NULL
     *
     * @var string
     * @ORM\Column(name="reference_id", type="integer")
     */
    protected $referenceId;

    /**
     * rank INT(10) UNSIGNED NOT NULL
     *
     * @var string
     * @ORM\Column(name="rank", type="integer")
     */
    protected $rank;

    /**
     * Destination of uploaded file (e.g. 'gallery', 'documents').
     *
     * destination VARCHAR(128)
     *
     * @var string
     * @ORM\Column(name="destination", type="string", length=128, nullable=true)
     */
    protected $destination;

    /**
     * mimetype VARCHAR(256) NOT NULL
     *
     * @var string
     * @ORM\Column(name="mimetype", type="string", length=256)
     */
    protected $mimetype;

    /**
     * Name of uploaded file w/ suffix (e.g. photo.jpg).
     *
     * name VARCHAR(256) NOT NULL
     *
     * @var string
     * @ORM\Column(name="name", type="string", length=256)
     */
    protected $name;

    /**
     * Temporary name of uploaded file w/o suffix (e.g. ./data/upload_52ceca2637abe).
     *
     * tmpname VARCHAR(256) NOT NULL
     *
     * @var string
     * @ORM\Column(name="tmpname", type="string", length=256)
     */
    protected $tmpName;

    /**
     * size INT(10) UNSIGNED NOT NULL
     *
     * @var int
     * @ORM\Column(name="size", type="integer")
     */
    protected $size;

    /**
     * description text
     *
     * @var string
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * File checked to deleting
     *
     * @var  bool
     */
    protected $_toDelete = false;

    /**
     * Creates a clone of an UploadEntity
     */
    public function __clone() {

        if ($this->getId()) {
            $this->setId(null);
        }
    }

    /**
     *
     * @return the $id
     */
    public function getId() {
        return $this->id;
    }

    /**
     *
     * @param int $id
     * @return \Ffb\Backend\Entity\UploadEntity
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     *
     * @return the $referenceType
     */
    public function getReferenceType() {
        return $this->referenceType;
    }

    /**
     *
     * @param string $referenceType
     * @return \Ffb\Backend\Entity\UploadEntity
     */
    public function setReferenceType($referenceType) {
        $this->referenceType = $referenceType;
        return $this;
    }

    /**
     *
     * @return the $referenceId
     */
    public function getReferenceId() {
        return $this->referenceId;
    }

    /**
     *
     * @param string $referenceId
     * @return \Ffb\Backend\Entity\UploadEntity
     */
    public function setReferenceId($referenceId) {
        $this->referenceId = $referenceId;
        return $this;
    }

    /**
     *
     * @return the $rank
     */
    public function getRank() {
        return $this->rank;
    }

    /**
     *
     * @param string $rank
     * @return \Ffb\Backend\Entity\UploadEntity
     */
    public function setRank($rank) {
        $this->rank = $rank;
        return $this;
    }

    /**
     *
     * @return the $mimetype
     */
    public function getMimetype() {
        return $this->mimetype;
    }

    /**
     *
     * @param string $mimetype
     * @return \Ffb\Backend\Entity\UploadEntity
     */
    public function setMimetype($mimetype) {
        $this->mimetype = $mimetype;
        return $this;
    }

    /**
     *
     * @return the $destination
     */
    public function getDestination() {
        return $this->destination;
    }

    /**
     *
     * @param string $destination
     * @return \Ffb\Backend\Entity\UploadEntity
     */
    public function setDestination($destination) {
        $this->destination = $destination;
        return $this;
    }

    /**
     *
     * @return the $name
     */
    public function getName() {
        return $this->name;
    }

    /**
     *
     * @param string $name
     * @return \Ffb\Backend\Entity\UploadEntity
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     *
     * @return the $tmpName
     */
    public function getTmpName() {
        return $this->tmpName;
    }

    /**
     *
     * @param string $tmpName
     * @return \Ffb\Backend\Entity\UploadEntity
     */
    public function setTmpName($tmpName) {
        $this->tmpName = $tmpName;
        return $this;
    }

    /**
     *
     * @return the $size
     */
    public function getSize() {
        return $this->size;
    }

    /**
     *
     * @param string $size
     * @return \Ffb\Backend\Entity\UploadEntity
     */
    public function setSize($size) {
        $this->size = $size;
        return $this;
    }

    /**
     *
     * @return the $description
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     *
     * @param string $description
     * @return \Ffb\Backend\Entity\UploadEntity
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @return the $_toDelete
     */
    public function getToDelete() {
        return $this->_toDelete;
    }


    /**
     * @param boolean $_toDelete
     * @return \Ffb\Backend\Entity\UploadEntity
     */
    public function setToDelete($_toDelete) {
        $this->_toDelete = $_toDelete;
        return $this;
    }
}