<?php

namespace Ffb\Common\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AbstractUserOwnedEntity
 *
 * This is the parent class for all entities which have a user as creator and
 * modifier and their respective dates as payload. It is the base class for most
 * of the "real" (i.e. not relational) entities of this application.
 *
 * @deprecated use AbstractUserOwnedEntity instead
 */
abstract class AbstractSequencedEntity extends AbstractUserOwnedEntity {}

