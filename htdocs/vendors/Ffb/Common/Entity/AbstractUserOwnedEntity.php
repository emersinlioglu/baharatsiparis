<?php

namespace Ffb\Common\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AbstractUserOwnedEntity
 *
 * This is the parent class for all entities which have a user as creator and
 * modifier and their respective dates as payload. It is the base class for most
 * of the "real" (i.e. not relational) entities of this application.
 */
abstract class AbstractUserOwnedEntity extends AbstractBaseEntity {

    /**
     * defines if an identity is required to persist this entity
     *
     * @var bool
     */
    const REQUIRES_IDENTITY = true;

    /**
     * "CREATOR_USER_ID" NUMBER(10,0)
     *
     * @var int
     * @ORM\Column(name="creator_user_id", type="integer", nullable=true)
     * @todo may be nullable for UserEntity!
     */
    protected $creatorUserId = 0;

    /**
     * "CREATED_USER_DATE" DATE NOT NULL ENABLE
     *
     * @var \DateTime
     * @ORM\Column(name="created_user_date", type="datetime", nullable=false)
     */
    protected $createdUserDate;

    /**
     * "MODIFIER_USER_ID" NUMBER(10,0)
     *
     * @var int
     * @ORM\Column(name="modifier_user_id", type="integer", nullable=true)
     * @todo may be nullable for UserEntity!
     */
    protected $modifierUserId = 0;

    /**
     * "MODIFIED_USER_DATE" DATE NOT NULL ENABLE
     *
     * @var \DateTime
     * @ORM\Column(name="modified_user_date", type="datetime", nullable=false)
     */
    protected $modifiedUserDate;

    /**
     */
    public function __construct() {
        //parent::__construct();

        //set modifier user DATE
        $date = new \DateTime();
        $this->setModifiedUserDate($date);
        //set creator user DATE
        if (is_null($this->getId())) {
            $this->setCreatedUserDate($date);
        }

        $this->_exchangeBL = array_merge($this->_exchangeBL, array(
            'creatorUserId',
            'createdUserDate',
            'modifierUserId',
            'modifiedUserDate'
        ));
    }

    /**
     *
     * @return the $creatorUserId
     */
    public function getCreatorUserId() {
        return $this->creatorUserId;
    }

    /**
     *
     * @param number $creatorUserId
     */
    public function setCreatorUserId($creatorUserId) {
        $this->creatorUserId = (int) $creatorUserId;
    }

    /**
     *
     * @return \DateTime
     */
    public function getCreatedUserDate() {
        return $this->createdUserDate;
    }

    /**
     *
     * @param \DateTime $createdUserDate
     */
    public function setCreatedUserDate($createdUserDate) {
        $this->createdUserDate = $createdUserDate;
    }

    /**
     *
     * @return the $modifierUserId
     */
    public function getModifierUserId() {
        return $this->modifierUserId;
    }

    /**
     *
     * @param number $modifierUserId
     */
    public function setModifierUserId($modifierUserId) {
        $this->modifierUserId = (int) $modifierUserId;
    }

    /**
     *
     * @return \DateTime
     */
    public function getModifiedUserDate() {
        return $this->modifiedUserDate;
    }

    /**
     *
     * @param \DateTime $modifiedUserDate
     */
    public function setModifiedUserDate($modifiedUserDate) {
        $this->modifiedUserDate = $modifiedUserDate;
    }

    /**
     * One function for Attendee and User to simple checks
     *
     * @return integer
     */
    public function getCreatorId() {
        return $this->getCreatorUserId();
    }
}
