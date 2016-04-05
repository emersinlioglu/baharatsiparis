<?php

namespace Ffb\Backend\Form\Fieldset;

use Ffb\Backend\Entity;

use Zend\Form\Fieldset;
use Zend\Stdlib\Hydrator;

/**
 *
 * @author erdal.mersinlioglu
 */
class AttributeGroupAttributeFieldset extends AbstractBackendFieldset {

    /**
     *
     * @param type $name
     * @param array $options Optional options for the element
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct($name = 'attribute-group-attribute', $options = array(), \Doctrine\ORM\EntityManager $em = null) {

        parent::__construct($name, $options);

        $this->setEm($em);
        $this->setHydrator(new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($em));
        $this->setObject(new Entity\AttributeGroupAttributeEntity());
        $this->setAttribute('class', 'fieldset-default fieldset-attribute-group-attribute');
        $this->_setElements();
    }

    /**
     * Define form elements.
     *
     * @see \Ffb\Backend\Form\AbstractBackendForm::_setElements()
     */
    protected function _setElements() {

        $this->add(array(
            'name' => 'id',
            'type' => 'Hidden'
        ));

      $this->add(array(
            'name' => 'sort',
            'type' => 'Text',
            'options' => array(
                'label' => 'LBL_ATTRIBUTE_GROUP_ATTRIBUTE_ORDER'
            )
        ));

    }

    /**
     * Returns validation rules.
     * This specifications are an array compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification() {
        return array(
            'sort' => array(
                'validators' => array(
                    $this->_validators['not_empty'],
                    $this->_validators['int']
                )
            )
        );
    }

}
