<?php

namespace Ffb\Backend\Form\Fieldset;

use Ffb\Backend\Entity;

use Zend\Form\Fieldset;
use Zend\Stdlib\Hydrator;

/**
 *
 * @author erdal.mersinlioglu
 */
class ProductGroupLangFieldset extends AbstractBackendFieldset {

    /**
     *
     * @param type $name
     * @param array $options Optional options for the element
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct($name = 'product-group-lang', $options = array(), \Doctrine\ORM\EntityManager $em = null) {
        parent::__construct($name, $options, $em);

        $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($this->getEm());

        $this->setEm($em);
        $this->setHydrator($hydrator);
        $this->setObject(new Entity\ProductGroupLangEntity());
        $this->setAttribute('class', 'fieldset-default fieldset-product-group-lang');
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
            'name' => 'name',
            'type' => 'Text',
            'options' => array(
                'label' => 'LBL_PRODUCT_GROUP_NAME',
                'label_options' => array('disable_html_escape' => true)
            )
        ));

        $this->add(array(
            'name' => 'title',
            'type' => 'Text',
            'options' => array(
                'label' => 'LBL_ATTRIBUTE_GROUP_TITLE'
            )
        ));

        $this->add(array(
            'name' => 'alias',
            'type' => 'Text',
            'options' => array(
                'label' => 'LBL_ATTRIBUTE_GROUP_ALIAS'
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
            'lang' => array(
                'validators' => array(
                    $this->_validators['not_empty'],
                    $this->_validators['int']
                )
            ),
            'name' => array(
                'allow_empty' => true,
                'validators' => array(
//                    $this->_validators['not_empty'],
                    $this->_validators['string_length_max_255']
                )
            ),
            'title' => array(
                'allow_empty' => true,
                'validators' => array(
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
