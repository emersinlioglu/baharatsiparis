<?php

namespace Ffb\Backend\Form;

use Ffb\Backend\Entity;
use Ffb\Backend\Form\Fieldset;

use Zend\InputFilter;

/**
 *
 * @author erdal.mersinlioglu
 */
class SearchForm extends AbstractBackendForm {

    /**
     *
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     */
    public function __construct($name = 'form-search-contingent', $options = array(), $em = null) {
        parent::__construct($name, $options);

        $this->setEm($em);

        $this->setAttribute('class', 'form-default form-search-contingent')
             ->_setElements();
    }

    /**
     *
     */
    protected function _setElements() {

        $this->add(array(
            'name' => 'search',
            'type' => 'Text',
            'options' => array(
                'label' => ''
            ),
            'attributes' => array(
                'placeholder' => 'PLH_SEARCH',
                'class' => 'search'
            )
        ));

        $this->add(array(
            'name' => 'isSystem',
            'type' => 'Select',
            'options' => array(
                //'label' => 'LBL_IS_SYSTEM',
                //'empty_option' => 'OPT_SYSTEM_PRODUCT_EMPTY',
                'value_options' => array(
                    '0' => 'OPT_NO_SYSTEM_PRODUCT',
                    '1' => 'OPT_SYSTEM_PRODUCT',
                )
            ),
            'attributes' => array(
                'class' => 'is-system'
            )
        ));

        $this->add(array(
            'name' => 'send',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'BTN_SUBMIT'
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
                'name' => 'search',
                'allow_empty' => true,
                'validators' => array(
                    $this->_validators['string_length_max_128']
                )
            ));

        $this->_inputFilter = $inputFilter;
        return $this->_inputFilter;
    }
}