<?php

namespace Ffb\Common\Form;

use Ffb\Common\I18n;
use Ffb\Common\DateFormatter;

use Zend\Form\Form;
use Zend\InputFilter;

/**
 *
 * @author ilja.schwarz
 */
abstract class AbstractForm extends Form implements InputFilter\InputFilterAwareInterface {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $_em;

    /**
     * @var string
     */
    private $_locale;

    /**
     * Filters array
     *
     * @var array
     */
    protected $_filters = array(
        'float'             => array('name' => 'Zend\I18n\Filter\NumberFormat', 'options' => array('locale' => 'en')),
        'null'              => array('name' => 'Zend\Filter\Null'/*, 'options' => array('type' => \Zend\Filter\Null::TYPE_STRING)*/),
        'null_empty_only'   => array('name' => 'Ffb\Common\Filter\Null'),
        'string_to_lower'   => array('name' => 'StringToLower'),
        'string_trim'       => array('name' => 'StringTrim'),
        'strip_tags'        => array('name' => 'StripTags', 'options' => array('allowTags' => array(), 'allowAttribs' => array())),
        'strip_tags_allow'  => array('name' => 'StripTags', 'options' => array('allowTags' => array('b','em','i','strong','p'), 'allowAttribs' => array())),
        'upload'            => array(
            'name'                => 'Zend\Filter\File\RenameUpload',
            'options'             => array(
                // Target directory or full filename path.
                'target'          => './public/temp/',
                //'target'          => '../../data/temp/',
                // Shall existing files be overwritten?
                'overwrite'       => false,
                // Shall target files have a random postfix attached?
                // The random postfix will be a uniqid('_') after the
                // file name and before the extension.
                'randomize'       => true,
                // When true, this filter will use the $_FILES[‘name’]
                // as the target filename. Otherwise, the default target
                // rules and the $_FILES['tmp_name'] will be used.
                'use_upload_name' => true
            )
        ),
        'move_upload'       => array(
            'name'                => 'Ffb\Common\Filter\File\RenameUpload',
            'options'             => array(
                // Target directory or full filename path.
                'target'          => './public/temp/',
                //'target'          => '../../data/temp/',
                // Shall existing files be overwritten?
                'overwrite'       => false,
                // Shall target files have a random postfix attached?
                // The random postfix will be a uniqid('_') after the
                // file name and before the extension.
                'randomize'       => true,
                // When true, this filter will use the $_FILES[‘name’]
                // as the target filename. Otherwise, the default target
                // rules and the $_FILES['tmp_name'] will be used.
                'use_upload_name' => true
            )
        )
    );

    /**
     * Validators array
     *
     * @var array
     */
    protected $_validators = array(
        'date'                    => array('name' => 'Date', 'options' => array('format' => 'm/d/Y', 'locale' => 'en')),
        'email_address'           => array('name' => 'EmailAddress'),
        'float'                   => array('name' => 'Float', 'options' => array('locale' => 'en')),
        'int'                     => array('name' => 'Int'),
        'positive_only'           => array('name' => 'Ffb\Common\Validator\PositiveOnly'),
        'not_empty'               => array('name' => 'NotEmpty'),
        'string_length_max_1'     => array('name' => 'string_length', 'options' => array('max' => 1)),
        'string_length_max_2'     => array('name' => 'string_length', 'options' => array('max' => 2)),
        'string_length_max_3'     => array('name' => 'string_length', 'options' => array('max' => 3)),
        'string_length_max_5'     => array('name' => 'string_length', 'options' => array('max' => 5)),
        'string_length_max_7'     => array('name' => 'string_length', 'options' => array('max' => 7)),
        'string_length_max_8'     => array('name' => 'string_length', 'options' => array('max' => 8)),
        'string_length_max_16'    => array('name' => 'string_length', 'options' => array('max' => 16)),
        'string_length_max_19'    => array('name' => 'string_length', 'options' => array('max' => 19)),
        'string_length_max_24'    => array('name' => 'string_length', 'options' => array('max' => 24)),
        'string_length_max_32'    => array('name' => 'string_length', 'options' => array('max' => 32)),
        'string_length_max_45'    => array('name' => 'string_length', 'options' => array('max' => 45)),
        'string_length_max_64'    => array('name' => 'string_length', 'options' => array('max' => 64)),
        'string_length_max_128'   => array('name' => 'string_length', 'options' => array('max' => 128)),
        'string_length_max_254'   => array('name' => 'string_length', 'options' => array('max' => 254)),
        'string_length_max_255'   => array('name' => 'string_length', 'options' => array('max' => 255)),
        'string_length_max_256'   => array('name' => 'string_length', 'options' => array('max' => 256)),
        'string_length_max_512'   => array('name' => 'string_length', 'options' => array('max' => 512)),
        'string_length_max_2048'  => array('name' => 'string_length', 'options' => array('max' => 2048)),
        'string_length_max_16384' => array('name' => 'string_length', 'options' => array('max' => 16384)),
        'string_length_min_4'     => array('name' => 'string_length', 'options' => array('min' => 4))
    );

    /**
     * Filter instance for input fields of this form.
     *
     * @var InputFilter\InputFilter
     */
    protected $_inputFilter;

    /**
     */
    abstract protected function _setElements();

    /**
     * Constructor.
     *
     * @param  null|int|string $name
     *         Optional name for the element
     * @param  array $options
     *         Optional options for the element
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct($name, $options = array(), \Doctrine\ORM\EntityManager $em = null) {

        parent::__construct($name, $options);

        $this->setEm($em);

        $this->setAttribute('method', 'post');

        $translator = I18n\Translator\Translator::Instance();
        $dateFormatter = DateFormatter\DateFormatter::getInstance();

        // set locale, filters & validators
        $this->_locale = $translator->getTranslator()->getTranslator()->getLocale();
        $this->_filters['float']['options']['locale']         = $this->_locale;
        $this->_validators['date']['options']['locale']       = $this->_locale;
        $this->_validators['date']['options']['format']       = $dateFormatter->getFormat($this->_locale);

        // intern validator work with english for doctrine only,
        // use filter to convert inputdata to intern format
        //$this->_validators['float']['options']['locale']     = 'en';

    }

    /**
     * EntityManager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEm() {
        return $this->_em;
    }

    /**
     *
     * @param \Doctrine\ORM\EntityManager $em
     * @return \Ffb\Backend\Form\AbstractBackendForm
     */
    public function setEm(\Doctrine\ORM\EntityManager $em = null) {
        $this->_em = $em;
        return $this;
    }

    /**
     *
     * @return InputFilter
     */
    public function getInputFilter() {
        return $this->_inputFilter;
    }

    /**
     *
     * @return array $_filters
     */
    public function getFilters() {
        return $this->_filters;
    }

    /**
     *
     * @return string
     */
    public function getLocale() {
        return $this->_locale;
    }

    /**
     *
     * @return array $_validators
     */
    public function getValidators() {
        return $this->_validators;
    }

    /**
     * Get array with element validators
     *
     * @param string $name
     * @return array
     * @throws \Exception If not element in form
     */
    public function getFieldValidators($name) {

        if (!$this->get($name)) {
            throw new \Exception('there is no element in form');
        }

        $valNames = array();
        if (!$this->getInputFilter() || !$this->getInputFilter()->has($name)) {
            return $valNames;
        }

        $validators = $this->getInputFilter()->get($name)->getValidatorChain()->getValidators();
        /* @var $validator \Zend\Validator\AbstractValidator */
        foreach ($validators as $validator) {

            $val = $validator['instance'];
            $valNames[get_class($val)] = $val->getOptions();

            // fix for Regex
            if ($val instanceof \Zend\Validator\Regex) {
                $valNames[get_class($val)]['pattern'] = $val->getPattern();
            }
        }
        return $valNames;
    }

    /**
     * Get mapped invalid fields.
     *
     * This method now is capable of gathering error messages of fields in
     * fieldsets too. The only drawback is, that I couldn't figure out how to
     * determine the fieldsets name.
     *
     * @param InputFilter\InputFilter $inputFilter containing invalid inputs
     * @return array
     */
    public function getInvalidFields(InputFilter\InputFilter $inputFilter = null) {

        if (is_null($inputFilter)) {
            $inputFilter = $this->getInputFilter();
        }

        $result = array();
        foreach ($inputFilter->getInvalidInput() as $name => $input) {

            //get error messages
            if ($input instanceof InputFilter\Input) {
                $messages = $input->getMessages();
            } else if ($input instanceof InputFilter\InputFilter) {
                $messages = $input->getMessages();
            } else if ($input instanceof InputFilter\CollectionInputFilter) {
                $messages = $this->getMessages();
            }

            $result[$name] = $messages;
        }

        return $result;
    }

    /**
     * Set invalid classname to invalid fields.
     *
     * @param array $invalidFields
     */
    public function setInvalidClassname(array $invalidFields = null) {

        if (!$invalidFields) {
            $invalidFields = $this->getInvalidFields();
        }

        //check is Valid
        foreach ($invalidFields as $name => $field) {

            if ($this->has($name)) {

                $className  = $this->get($name)->getAttribute('class');
                $className .= ' invalid';
                $this->get($name)->setAttribute('class', $className);
            }
        }
    }

    /**
     * Translate element value with Translator
     *
     * @param array|string $mixed
     * @param Fieldset $parent
     * @return AbstractBackendForm
     */
    public function translateElementValue($mixed, $parent = null) {

        if (is_string($mixed)) {
            $names = array($mixed);
        } else {
            $names = $mixed;
        }

        if ($parent === null) {
            $parent = $this;
        }

        $translator = I18n\Translator\Translator::Instance();

        foreach ($names as $name) {

            // check if fieldset
            if (is_array($name)) {

                // go throug keys and update elements
                foreach ($name as $fieldsetName => $fieldsetElements) {

                    if (   $parent->has($fieldsetName)
                        && $parent->get($fieldsetName) instanceof AbstractFieldset
                    ) {
                        $this->translateElementValue($fieldsetElements, $parent->get($fieldsetName));
                    }

                    if (   $parent->has($fieldsetName)
                        && $parent->get($fieldsetName) instanceof \Zend\Form\Element\Collection
                    ) {

                        $this->translateElementValue($fieldsetElements, $parent->get($fieldsetName)->getTargetElement());
                    }
                }
            } else {

                // check element
                if ($parent->has($name)) {

                    // get element
                    $element = $parent->get($name);

                    // translate value if exist
                    if ($element->getValue()) {
                        $element->setValue($translator::translate($element->getValue()));
                    }

                    if ($element->getAttribute('data-placeholder')) {
                        $element->setAttribute(
                            'data-placeholder',
                            $translator::translate($element->getAttribute('data-placeholder'))
                        );
                    }
                }
            }
        }

        return $this;
    }

    /**
     */
    public function disable() {

        /**
         *
         * @param \Zend\Form\Fieldset $fieldset
         * @return array
         */
        function getFieldsetsRecursivly(\Zend\Form\Fieldset $fieldset) {

            $fieldsets = array($fieldset);

            foreach ($fieldset->getFieldsets() as $subFieldset) {

                if ($subFieldset instanceof \Zend\Form\Element\Collection) {
                    $subFieldset = $subFieldset->getTargetElement();
                }

                $fieldsets = array_merge($fieldsets, getFieldsetsRecursivly($subFieldset));
            }

            return $fieldsets;
        }

        foreach (getFieldsetsRecursivly($this) as $fieldset) {

            foreach ($fieldset->getElements() as $element) {
                $element->setAttribute('disabled', 'disabled');
            }
        }
    }
}