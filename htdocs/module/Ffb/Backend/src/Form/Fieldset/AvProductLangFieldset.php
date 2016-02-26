<?php

namespace Ffb\Backend\Form\Fieldset;

use Ffb\Backend\Entity;

use Zend\Form\Fieldset;
use Zend\Stdlib\Hydrator;

/**
 *
 * @author erdal.mersinlioglu
 */
class AvProductLangFieldset extends AbstractBackendFieldset {

    /**
     *
     * @param type $name
     * @param array $options Optional options for the element
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct($name = 'product-lang', $options = array(), \Doctrine\ORM\EntityManager $em = null) {

        parent::__construct($name, $options);

        $this->setEm($em);
        $this->setHydrator(new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($em));
        $this->setObject(new Entity\ProductLangEntity());
        $this->setAttribute('class', 'fieldset-default fieldset-product-lang');
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

        $this->add(array(
            'name' => 'alias',
            'type' => 'Text',
            'options' => array(
                'label' => 'LBL_PRODUCT_ALIAS'
            )
        ));

        $this->add(array(
            'name' => 'description',
            'type' => 'Text',
            'options' => array(
                'label' => 'LBL_PRODUCT_DESCRIPTION'
            )
        ));

        $this->add(array(
            'name' => 'name',
            'type' => 'Text',
            'options' => array(
                'label' => 'LBL_PRODUCT_NAME',
                'label_options' => array('disable_html_escape' => true)
            )
        ));

        $this->add(array(
            'type' => 'Ffb\Common\Form\Element\Collection',
            'name' => 'attributeValues',
            'options' => array(
                'count'          => 0,
                'allow_add'      => true,
                'allow_remove'   => true,
                'target_element' => new AvAttributeValueFieldset('attribute-value', array(), $this->getEm())
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
            'name' => array(
                'allow_empty' => true,
                'validators' => array(
                    //$this->_validators['not_empty'],
                    $this->_validators['string_length_max_255']
                )
            ),
            'description' => array(
                'allow_empty' => true,
                'validators' => array(
                    //$this->_validators['not_empty'],
                    $this->_validators['string_length_max_255']
                )
            ),
            'alias' => array(
                'allow_empty' => true,
                'validators' => array(
                    $this->_validators['string_length_max_255']
                ),
                'filters' => array(
                    $this->_filters['strip_tags'],
                    $this->_filters['string_trim']
                )
            )
        );
    }

}
