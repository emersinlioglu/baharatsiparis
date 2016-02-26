<?php

namespace Ffb\Backend\Form;

use Ffb\Backend\Entity;
use Ffb\Backend\Form\Fieldset;

use Zend\InputFilter;

/**
 * ProductGroupForm
 *
 * @author erdal.mersinlioglu
 */
class ProductGroupForm extends AbstractBackendForm {

    /**
     *
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     * @param  \Doctrine\ORM\EntityManager  $em
     */
    public function __construct($name = 'form-product-group', $options = array(), \Doctrine\ORM\EntityManager $em = null) {
        parent::__construct($name, $options, $em);

        $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($this->getEm());

        $this->setHydrator($hydrator)
             ->setObject(new Entity\ProductGroupEntity())
             ->setAttribute('class', 'form-default form-product-group')
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
                'name' => 'parent',
                'type' => 'DoctrineModule\Form\Element\ObjectSelect',
                'options' => array(
                    'object_manager' => $this->getEm(),
                    'target_class'   => 'Ffb\Backend\Entity\ProductGroupEntity',
                    'property'       => 'id',
                    'label'          => 'LBL_PRODUCT_GROUP_PARENT',
                    'empty_option'   => 'OPT_PRODUCT_GROUP_PARENT_EMPTY'
                ),
            ))
            ->add(array(
                'type' => 'Ffb\Common\Form\Element\Collection',
                'name' => 'translations',
                'options' => array(
                    'count'          => 0,
                    'allow_add'      => true,
                    'allow_remove'   => true,
                    'target_element' => new Fieldset\ProductGroupLangFieldset('translations', array(), $this->getEm())
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
                'name' => 'parent',
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