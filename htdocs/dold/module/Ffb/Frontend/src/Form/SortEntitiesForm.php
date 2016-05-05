<?php

namespace Ffb\Frontend\Form;

use Ffb\Backend\Entity;
use Ffb\Frontend\Form\Fieldset;

use Zend\InputFilter;

/**
 * AttributeGroupForm
 *
 * @author erdal.mersinlioglu
 */
class SortEntitiesForm extends AbstractBackendForm {

    /**
     *
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     * @param  \Doctrine\ORM\EntityManager  $em
     */
    public function __construct($name = 'form-sort-entities', $options = array(), \Doctrine\ORM\EntityManager $em = null) {
        parent::__construct($name, $options, $em);

        $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($this->getEm());

        $this->setHydrator($hydrator)
             //->setObject(new Entity\AttributeGroupEntity())
             ->setAttribute('class', 'form-default form-sort-entities')
             ->_setElements();

        $this->translateElementValue(array('send'));
    }

    /**
     *
     */
    protected function _setElements() {

        $collectionFieldName = $this->options['collectionFieldName'];
        $fieldsetName = 'Ffb\\Backend\\Form\\Fieldset\\' . $this->options['fieldsetName'];

        $this
            ->add(array(
                'name' => 'id',
                'type' => 'Hidden'
            ))
            ->add(array(
                'type' => 'Ffb\Common\Form\Element\Collection',
                'name' => $collectionFieldName,
                'options' => array(
                    'count'          => 0,
                    'allow_add'      => true,
                    'allow_remove'   => true,
                    'target_element' => new $fieldsetName($collectionFieldName, array(), $this->getEm())
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

        $this->attachInputFilterDefaults($inputFilter, $this);
        $this->hasAddedInputFilterDefaults = true;

        $this->_inputFilter = $inputFilter;
        return $this->_inputFilter;
    }
}