<?php

namespace Ffb\Frontend\Form;

use Zend\InputFilter;

/**
 *
 * @author erdal.mersinlioglu
 */
class LoginForm extends AbstractBackendForm {

    /**
     *
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     */
    public function __construct($name = 'form-login', $options = array()) {
        parent::__construct($name, $options);

        $this->setAttribute('class', 'form-default form-login form-signin');
        $this->_setElements();
    }

    /**
     *
     */
    protected function _setElements() {

        $this
            ->add(array(
                'name' => 'email',
                'type' => 'Text',
                'options' => array(
                    'label' => 'LBL_EMAIL'
                ),
                'attributes' => array(
                    'class' => 'form-control',
                    'value' => '',
                    'autocomplete' => 'off',
                    'maxlength' => '255'
                )
            ))
            //sysadmin@4fb.de
            //sysadmin
            ->add(array(
                'name' => 'password',
                'type' => 'Password',
                'options' => array(
                    'label' => 'LBL_PASSWORD'
                ),
                'attributes' => array(
                    'class' => 'form-control',
                    'value' => '',
                    'autocomplete' => 'off',
                    'maxlength' => '255'
                )
            ))
            ->add(array(
                'name' => 'login',
                'type' => 'Submit',
                'attributes' => array(
                    'value' => 'BTN_LOGIN',
                    'class' => 'btn btn-lg btn-primary btn-block'
                ),
            ));
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Form\Form::getInputFilter()
     */
    public function getInputFilter() {

        if ($this->_inputFilter) {
            return $this->_inputFilter;
        }

        $inputFilter = new InputFilter\InputFilter();
        $inputFilter
            ->add(array(
                'name' => 'email',
                'validators' => array(
                    $this->_validators['not_empty']
                ),
                'filters' => array(
                    $this->_filters['strip_tags'],
                    $this->_filters['string_trim']
                )
            ))
            ->add(array(
                'name'       => 'password',
                'validators' => array(
                    $this->_validators['not_empty']
                ),
                'filters'    => array(
                    $this->_filters['strip_tags'],
                    $this->_filters['string_trim']
                )
            ));

        $this->_inputFilter = $inputFilter;
        return $this->_inputFilter;
    }

}