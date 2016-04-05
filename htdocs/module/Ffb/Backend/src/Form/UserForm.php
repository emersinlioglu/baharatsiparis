<?php

namespace Ffb\Backend\Form;

use Ffb\Backend\Entity;
use Ffb\Backend\Form\Fieldset;

use Zend\InputFilter;

/**
 * DocumentFieldset: this class is commented cause we don't need the feature
 * to upload multiple files at once. Instead each file will be uploaded through
 * a single Ajax request.
 *
 * @author erdal.mersinlioglu
 */
class UserForm extends AbstractBackendForm {

    /**
     *
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     * @param  \Doctrine\ORM\EntityManager  $em
     */
    public function __construct($name = 'form-user', $options = array(), $em = null) {
        parent::__construct($name, $options, $em);

        $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($this->getEm());

        $this->setHydrator($hydrator)
             ->setObject(new Entity\UserEntity())
             ->setAttribute('class', 'form-default form-user')
             ->_setElements();

        $this->translateElementValue(array('send', 'delete'));
    }

    /**
     *
     */
    protected function _setElements() {

        $this
            ->add(array(
                'name' => 'name',
                'type' => 'Text',
                'options' => array(
                    'label' => 'LBL_USER_NAME'
                )
            ))
            ->add(array(
                'name' => 'email',
                'type' => 'Text',
                'options' => array(
                    'label' => 'LBL_USER_EMAIL'
                )
            ))
            ->add(array(
                'name' => 'newPassword',
                'type' => 'Password',
                'options' => array(
                    'label' => 'LBL_USER_PASSWORD'
                )
            ))
            ->add(array(
                'name' => 'isLocked',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'LBL_USER_IS_LOCKED'
                )
            ))
            ->add(array(
                'name' => 'allowProducts',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'LBL_USER_ALLOW_PRODUCTS'
                )
            ))
            ->add(array(
                'name' => 'allowAttributes',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'LBL_USER_ALLOW_ATTRIBUTES'
                )
            ))
            ->add(array(
                'name' => 'allowTemplates',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'LBL_USER_ALLOW_TEMPLATES'
                )
            ))
            ->add(array(
                'name' => 'allowAdmin',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'LBL_USER_ALLOW_ADMIN'
                )
            ))
            ->add(array(
                'name' => 'allowDelete',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'LBL_USER_ALLOW_DELETE'
                )
            ))
            ->add(array(
                'name' => 'allowEdit',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'LBL_USER_ALLOW_EDIT'
                )
            ))
            ->add(array(
                'name' => 'delete',
                'type' => 'Submit',
                'attributes' => array(
                    'class' => 'button red delete',
                    'value' => 'BTN_DELETE'
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
                'name' => 'name',
                'validators' => array(
                    $this->_validators['not_empty'],
                    $this->_validators['string_length_max_256']
                )
            ))
            ->add(array(
                'name' => 'email',
                'validators' => array(
                    $this->_validators['not_empty'],
                    $this->_validators['email_address'],
                    $this->_validators['string_length_max_256']
                )
            ))
            ->add(array(
                'name' => 'newPassword',
                'allow_empty' => true,
                'validators' => array(
                    $this->_validators['string_length_max_256']
                )
            ))
            ->add(array(
                'name' => 'isLocked',
                'validators' => array(
                    $this->_validators['not_empty'],
                    $this->_validators['int']
                )
            ))
            ->add(array(
                'name' => 'allowProducts',
                'validators' => array(
                    $this->_validators['not_empty'],
                    $this->_validators['int']
                )
            ))
            ->add(array(
                'name' => 'allowAttributes',
                'validators' => array(
                    $this->_validators['not_empty'],
                    $this->_validators['int']
                )
            ))
            ->add(array(
                'name' => 'allowTemplates',
                'validators' => array(
                    $this->_validators['not_empty'],
                    $this->_validators['int']
                )
            ))
            ->add(array(
                'name' => 'allowAdmin',
                'validators' => array(
                    $this->_validators['not_empty'],
                    $this->_validators['int']
                )
            ))
            ->add(array(
                'name' => 'allowDelete',
                'validators' => array(
                    $this->_validators['not_empty'],
                    $this->_validators['int']
                )
            ))
            ->add(array(
                'name' => 'allowEdit',
                'validators' => array(
                    $this->_validators['not_empty'],
                    $this->_validators['int']
                )
            ));

        $this->_inputFilter = $inputFilter;
        return $this->_inputFilter;
    }
}