<?php

namespace Ffb\Frontend\Form;

use Ffb\Backend\Entity;
use Ffb\Frontend\Form\Fieldset;

use Zend\InputFilter;

/**
 * AttributeForm
 *
 * @author erdal.mersinlioglu
 */
class AttributeForm extends AbstractBackendForm {

    /**
     *
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     * @param  \Doctrine\ORM\EntityManager  $em
     */
    public function __construct($name = 'form-attribute', $options = array(), \Doctrine\ORM\EntityManager $em = null) {
        parent::__construct($name, $options, $em);

        $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($this->getEm());
        //$hydrator->addStrategy('languages', new \DoctrineModule\Stdlib\Hydrator\Strategy\AllowRemoveByReference());

        $this->setHydrator($hydrator)
             ->setObject(new Entity\AttributeEntity())
             ->setAttribute('class', 'form-default form-attribute')
             ->_setElements();

        $this->translateElementValue(array('send'));
    }

    /**
     *
     */
    protected function _setElements() {

        $this
            ->add(array(
                'name' => 'id',
                'type' => 'Hidden'
            ))
            ->add(array(
                'name' => 'type',
                'type' => 'Select',
                'options' => array(
                    'label' => 'LBL_ATTRIBUTE_TYPE'
                )
            ))
            ->add(array(
                'name' => 'length',
                'type' => 'Text',
                'options' => array(
                    'label' => 'LBL_ATTRIBUTE_LENGTH'
                )
            ))
            ->add(array(
                'name' => 'isUppercase',
                'type' => 'Select',
                'options' => array(
                    'label' => 'LBL_ATTRIBUTE_IS_UPPERCASE',
                    'value_options' => array(
                        '' => 'LBL_ATTRIBUTE_NO_TRANSFORMATION',
                        '1' => 'LBL_ATTRIBUTE_UPPERCASE',
                        '0' => 'LBL_ATTRIBUTE_NOT_UPPERCASE'
                    )
                )
            ))
            ->add(array(
                'name' => 'isMultiSelect',
                'type' => 'Select',
                'options' => array(
                    'label' => 'LBL_ATTRIBUTE_IS_MULTI_SELECT',
                    'value_options' => array(
                        '1' => 'OPT_ATTRIBUTE_MULTI_SELECT',
                        '0' => 'OPT_ATTRIBUTE_NOT_MULTI_SELECT',
                    )
                )
            ))
            ->add(array(
                'name' => 'optionValues',
                'type' => 'Text',
                'options' => array(
                    'label' => 'LBL_ATTRIBUTE_OPTION_VALUES'
                ),
                'attributes' => array(
                    'placeholder' => 'PLH_ATTRIBUTE_OPTION_VALUES'
                )
            ))
            ->add(array(
                'type' => 'Ffb\Common\Form\Element\Collection',
                'name' => 'translations',
                'options' => array(
                    'count'          => 0,
                    'allow_add'      => true,
                    'allow_remove'   => false,
                    'target_element' => new Fieldset\AttributeLangFieldset('translations', array(), $this->getEm())
                )
            ))
            ->add(array(
                'name' => 'send',
                'type' => 'Submit',
                'attributes' => array(
                    'class' => 'button green save',
                    'value' => 'BTN_SAVE'
                )
            ));
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Form\Form::getInputFilter()
     * @see http://framework.zend.com/manual/2.1/en/modules/zend.input-filter.file-input.html
     */
    public function getInputFilter() {

        if ($this->_inputFilter) {
            return $this->_inputFilter;
        }

        $inputFilter = new InputFilter\InputFilter();
        $inputFilter
            ->add(array(
                'name' => 'type',
                'validators' => array(
                    $this->_validators['not_empty'],
                    $this->_validators['int']
                )
            ))
            ->add(array(
                'name' => 'isUppercase',
                'allow_empty' => true,
                'validators' => array(
                    $this->_validators['int']
                ),
                'filters' => array(
                    $this->_filters['null']
                )
            ));

        $this->attachInputFilterDefaults($inputFilter, $this);
        $this->hasAddedInputFilterDefaults = true;

        $this->_inputFilter = $inputFilter;
        return $this->_inputFilter;
    }
}