<?php

namespace Ffb\Frontend\Form;

use Ffb\Backend\Entity;
use Ffb\Frontend\Form\Fieldset;

use Zend\InputFilter;

/**
 * TemplateForm
 *
 * @author erdal.mersinlioglu
 */
class TemplateForm extends AbstractBackendForm {

    /**
     *
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     * @param  \Doctrine\ORM\EntityManager  $em
     */
    public function __construct($name = 'form-template', $options = array(), \Doctrine\ORM\EntityManager $em = null) {
        parent::__construct($name, $options, $em);

        $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($this->getEm());

        $this->setHydrator($hydrator)
             ->setObject(new Entity\TemplateEntity())
             ->setAttribute('class', 'form-default form-template')
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
                'name' => 'name',
                'type' => 'Text',
                'options' => array(
                    'label' => 'LBL_TEAMPLATE_NAME'
                )
            ))
//            ->add(array(
//                'type' => 'Ffb\Common\Form\Element\Collection',
//                'name' => 'translations',
//                'options' => array(
//                    'count'          => 0,
//                    'allow_add'      => true,
//                    'allow_remove'   => false,
//                    'target_element' => new Fieldset\AttributeLangFieldset('translations', array(), $this->getEm())
//                )
//            ))
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
                    $this->_validators['string_length_max_255']
                )
            ));

        $this->attachInputFilterDefaults($inputFilter, $this);
        $this->hasAddedInputFilterDefaults = true;

        $this->_inputFilter = $inputFilter;
        return $this->_inputFilter;
    }
}