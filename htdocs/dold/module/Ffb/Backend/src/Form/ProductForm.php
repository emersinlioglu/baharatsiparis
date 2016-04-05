<?php

namespace Ffb\Backend\Form;

use Ffb\Backend\Entity;
use Ffb\Backend\Form\Fieldset;

use Zend\InputFilter;

/**
 * ProductForm
 *
 * @author erdal.mersinlioglu
 */
class ProductForm extends AbstractBackendForm {

    /**
     *
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     * @param  \Doctrine\ORM\EntityManager  $em
     */
    public function __construct($name = 'form-product', $options = array(), \Doctrine\ORM\EntityManager $em = null) {
        parent::__construct($name, $options, $em);

        $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($this->getEm());
        //$hydrator->addStrategy('languages', new \DoctrineModule\Stdlib\Hydrator\Strategy\AllowRemoveByReference());

        $this->setHydrator($hydrator)
            ->setObject(new Entity\ProductEntity())
            ->setAttribute('class', 'form-default form-product')
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
                'name' => 'number',
                'type' => 'Text',
                'options' => array(
                    'label' => 'LBL_PRODUCT_NUMBER'
                )
            ))
            ->add(array(
                'name' => 'price',
                'type' => 'Text',
                'options' => array(
                    'label' => 'LBL_PRODUCT_PRICE'
                )
            ))
            ->add(array(
                'name' => 'online',
                'type' => 'Select',
                'options' => array(
                    'label' => 'LBL_PRODUCT_ONLINE',
                    'value_options' => array(
                        '1' => 'OPT_ACTIVATED',
                        '0' => 'OPT_DEACTIVATED'
                    )
                )
            ))
            ->add(array(
                'name' => 'isSystem',
                'type' => 'Select',
                'options' => array(
                    'label' => 'LBL_BASIC_INFORMATION',
                    //'empty_option' => 'OPT_SYSTEM_PRODUCT_EMPTY',
                    'value_options' => array(
                        '1' => 'OPT_SYSTEM_PRODUCT',
                        '0' => 'OPT_NO_SYSTEM_PRODUCT'
                    )
                )
            ))
            ->add(array(
                'name' => 'imageUrl',
                'type' => 'Text',
                'options' => array(
                    'label' => 'LBL_PRODUCT_IMAGE_URL'
                )
            ))
            ->add(array(
                'name' => 'isRoot',
                'type' => 'Hidden',
                'attributes' => array(
                    'value' => 0
                )
            ))
            ->add(array(
                'name' => 'parent',
                'type' => 'DoctrineModule\Form\Element\ObjectSelect',
                'options' => array(
                    'label'          => 'LBL_PRODUCT_PARENT',
                    'object_manager' => $this->getEm(),
                    'target_class'   => 'Ffb\Backend\Entity\ProductEntity',
                    'property'       => 'id',
                    //'is_method'      => true,
                    'empty_option'   => 'OPT_SELECT_PRODUCT_PARENT',
                    //'label_generator' => function($product) {
                    //    return $product->getCurrentTranslation()->getName();
                    //}
                ),
                'attributes' => array(
                    'class'    => 'parent-product',
                )
            ))
            ->add(array(
                'type' => 'Ffb\Common\Form\Element\Collection',
                'name' => 'translations',
                'options' => array(
                    'count'          => 0,
                    'allow_add'      => true,
                    'allow_remove'   => false,
                    'target_element' => new Fieldset\AvProductLangFieldset('translations', array(), $this->getEm())
                )
            ))
            ->add(array(
                'name' => 'send',
                'type' => 'Submit',
                'attributes' => array(
                    'class' => 'button gray',
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
                )
            ))
            ->add(array(
                'name' => 'online',
                'allow_empty' => true,
                'validators' => array(
                    $this->_validators['int']
                )
            ))
            ->add(array(
                'name' => 'isSystem',
                'validators' => array(
                    $this->_validators['not_empty'],
                    $this->_validators['int']
                )
            ))
            ->add(array(
                'name' => 'isRoot',
                'allow_empty' => true,
                'validators' => array(
                    $this->_validators['int']
                )
            ))
            ->add(array(
                'name' => 'imageUrl',
                'allow_empty' => true,
                'validators' => array(
                    $this->_validators['string_length_max_256']
                )
            ))
                ;

        $this->attachInputFilterDefaults($inputFilter, $this);
        $this->hasAddedInputFilterDefaults = true;

        $this->_inputFilter = $inputFilter;
        return $this->_inputFilter;
    }
}