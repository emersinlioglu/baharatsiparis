<?php

namespace Ffb\Frontend\Form\Fieldset;

use Ffb\Backend\Entity;

use Ffb\Frontend\Model\AttributeValueModel;
use Zend\Form\Fieldset;
use Zend\Stdlib\Hydrator;

/**
 *
 * @author erdal.mersinlioglu
 */
class AvAttributeValueFieldset extends AbstractBackendFieldset {

    /**
     *
     * @param type $name
     * @param array $options Optional options for the element
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct($name = 'attribute-value', $options = array(), \Doctrine\ORM\EntityManager $em = null) {
        parent::__construct($name, $options);

        $this->setEm($em);
        $this->setHydrator(new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($em));
        $this->setObject(new Entity\AttributeValueEntity());
        $this->setAttribute('class', 'fieldset-default fieldset-attribute-value');
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
            'name' => 'value',
            'type' => 'Text',
            'options' => array(
                'label' => 'LBL_VALUE',
                'label_options' => array(
                    'disable_html_escape' => true
                )
            )
        ));

        $this->add(array(
            'name' => 'valueMin',
            'type' => 'Text',
            'options' => array(
                'label' => 'LBL_VALUE_MIN',
                'label_options' => array(
                    'disable_html_escape' => true
                )
            ),
            'attributes' => array(
                'isValueMin' => true
            )
        ));

        $this->add(array(
            'name' => 'valueMax',
            'type' => 'Text',
            'options' => array(
                'label' => '&nbsp;',
                'label_options' => array(
                    'disable_html_escape' => true
                ),
            ),
            'attributes' => array(
                'isValueMax' => true
            )
        ));
        $this->add(array(
            'name' => 'description',
            'type' => 'Text',
            'options' => array(
                'label' => 'LBL_VALUE_MAX'
            )
        ));
        $this->add(array(
            'name' => 'isInherited',
            'type' => 'Checkbox',
            'options' => array(
                'label' => 'LBL_IS_INHERITED'
            )
        ));
        $this->add(array(
            'name' => 'referenceType',
            'type' => 'Hidden',
            'options' => array(
                //'label' => 'LBL_VALUE_MAX'
            )
        ));

        // render as hidden element
        $this->add(array(
            'name' => 'productLang',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'options' => array(
                'object_manager' => $this->getEm(),
                'target_class'   => 'Ffb\Backend\Entity\ProductLangEntity',
                'property'       => 'id'
            ),
        ));

        $attributeLangFs = new AvAttributeLangFieldset('attribute-lang', array(), $this->getEm());
        $attributeLangFs->setName('attributeLang');
        $this->add($attributeLangFs);

        $attributeGroupFs = new AvAttributeGroupFieldset('attribute-group', array(), $this->getEm());
        $attributeGroupFs->setName('attributeGroup');
        $this->add($attributeGroupFs);

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
            'value' => array(
                'allow_empty' => true,
                'validators' => array(
//                    $this->_validators['string_length_max_255']
                ),
                'filters' => array(
                    array(
                        "name" => "Callback",
                        "options" => array(
                            "callback" => function($value) {

                                if (is_array($value)) {
                                    $value = AttributeValueModel::encodeOptionValues($value);
                                }
                                return $value;
                            }
                        )
                    )
                )
            ),
            'value_min' => array(
                'allow_empty' => true,
                'validators' => array(
//                    $this->_validators['string_length_max_255']
                )
            ),
            'value_max' => array(
                'allow_empty' => true,
                'validators' => array(
//                    $this->_validators['string_length_max_255']
                )
            ),
            'description' => array(
                'allow_empty' => true,
                'validators' => array(
//                    $this->_validators['string_length_max_255']
                )
            ),
            'isInherited' => array(
                'allow_empty' => true,
                'validators' => array(
//                    $this->_validators['not_empty'],
//                    $this->_validators['string_length_max_255']
                )
            ),
            'productLang' => array(
                'allow_empty' => true,
                'validators' => array(
                    $this->_validators['int']
                )
            )
        );
    }

}
