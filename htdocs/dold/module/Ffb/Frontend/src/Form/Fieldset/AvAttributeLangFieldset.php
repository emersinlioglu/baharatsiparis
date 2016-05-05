<?php

namespace Ffb\Frontend\Form\Fieldset;

use Ffb\Backend\Entity;

use Zend\Form\Fieldset;
use Zend\Stdlib\Hydrator;

/**
 *
 * @author erdal.mersinlioglu
 */
class AvAttributeLangFieldset extends AbstractBackendFieldset {

    /**
     *
     * @param type $name
     * @param array $options Optional options for the element
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct($name = 'attribute-lang', $options = array(), \Doctrine\ORM\EntityManager $em = null) {

        parent::__construct($name, $options);

        $this->setEm($em);
        $this->setHydrator(new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($em));
        $this->setObject(new Entity\AttributeLangEntity());
        $this->setAttribute('class', 'fieldset-default fieldset-attribute-lang');
        $this->_setElements();
    }

    /**
     * Define form elements.
     *
     * @see \Ffb\Frontend\Form\AbstractBackendForm::_setElements()
     */
    protected function _setElements() {

        $this->add(array(
            'name' => 'id',
            'type' => 'Hidden'
        ));

        // render as hidden element
        $this->add(array(
            'name' => 'lang',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'options' => array(
                'object_manager' => $this->getEm(),
                'target_class'   => 'Ffb\Backend\Entity\LangEntity',
                'property'       => 'id'
            ),
        ));

        $attributeFs = new AvAttributeFieldset('attribute', array(), $this->getEm());
        $attributeFs->setName('translationTarget');
        $this->add($attributeFs);
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
            'id' => array(
                'allow_empty' => true,
                'validators' => array(
//                    $this->_validators['not_empty'],
                    $this->_validators['int']
                )
            ),
            'lang' => array(
                'allow_empty' => true,
                'validators' => array(
//                    $this->_validators['not_empty'],
                    $this->_validators['int']
                )
            ),
        );
    }

}
