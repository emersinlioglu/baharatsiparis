<?php

namespace Ffb\Common\Form;

use Zend\InputFilter;

/**
 * @author
 */
abstract class AbstractUploadForm extends AbstractForm {

    /**
     *
     * @var string
     */
    protected $_tempUploadFolder;

    /**
     *
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     */
    public function __construct($name = 'upload-form', $options = array()) {
        parent::__construct($name, $options);

        $this->setAttribute('enctype', 'multipart/form-data');
        $this->_setElements();
    }

    /**
     * Define form elements.
     *
     * @see \Ffb\Tms\Form\AbstractTmsForm::_setElements()
     */
    protected function _setElements() {

        $this->add(array(
            'name' => 'destination',
            'type' => 'Hidden'
        ));

        $this->add(array(
            'name' => 'token',
            'type' => 'Hidden'
        ));

        $this->add(array(
            'name' => 'referenceType',
            'type' => 'Hidden'
        ));

        $this->add(array(
            'name' => 'referenceId',
            'type' => 'Hidden'
        ));

        $this->add(array(
            'name' => 'id',
            'type' => 'Hidden'
        ));

        $this->add(array(
            'name' => 'uploadType',
            'type' => 'Hidden'
        ));

        $this->add(array(
            'name' => 'upload',
            'type' => 'File'
        ));

        $this->add(array(
            'name' => 'description',
            'type' => 'Textarea',
            'options' => array(
                'label' => 'LBL_DESCRIPTION'
            )
        ));

        $this->add(array(
            'name' => 'filepath',
            'type' => 'Text',
            'options' => array(
                'label' => ''
            ),
            'attributes' => array(
                'readonly' => 'readonly'
            )
        ));

        $this->add(array(
            'name' => 'send',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'submit'
            )
        ));
    }

    /**
     * @return InputFilter
     * @see \Ffb\Common\Form\AbstractForm::getInputFilter()
     * @see http://framework.zend.com/manual/2.1/en/modules/zend.input-filter.file-input.html
     */
    public function getInputFilter() {

        if ($this->_inputFilter) {
            return $this->_inputFilter;
        }

        $inputFilter = new InputFilter\InputFilter();

        $v = $this->_validators;
        $f = $this->_filters;

        $uploadFilter                      = $this->_filters['upload'];
        if ($this->_tempUploadFolder) {
            $uploadFilter['options']['target'] = $this->_tempUploadFolder;
        }

        $inputFilter->add(array(
            'name'       => 'upload',
            'filters'    => array($uploadFilter),
            'validators' => array($v['not_empty'])
        ));

        $inputFilter->add(array(
            'name'        => 'destination',
            'allow_empty' => true,
            'validators'  => array(),
            'filters'     => array($f['strip_tags'], $f['string_trim'])
        ));

        $inputFilter->add(array(
            'name'        => 'description',
            'allow_empty' => true,
            'validators'  => array(),
            'filters'     => array($f['strip_tags'], $f['string_trim'])
        ));

        $this->attachInputFilterDefaults($inputFilter, $this);
        $this->hasAddedInputFilterDefaults = true;

        $this->_inputFilter = $inputFilter;
        return $this->_inputFilter;
    }

    /**
     * @return string $_tempUploadFolder
     */
    public function getTempUploadFolder() {
        return $this->_tempUploadFolder;
    }

    /**
     * @param string $_tempUploadFolder
     */
    public function setTempUploadFolder($_tempUploadFolder) {
        $this->_tempUploadFolder = $_tempUploadFolder;
    }
}
