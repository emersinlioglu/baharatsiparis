<?php

namespace Ffb\Common\Form;

use Ffb\Common\I18n;
use Ffb\Common\DateFormatter;

use Zend\Form\Fieldset;
use Zend\InputFilter;

/**
 *
 * @author ilja.schwarz
 */
abstract class AbstractFieldset extends Fieldset implements InputFilter\InputFilterProviderInterface {

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
        'null'              => array('name' => 'Zend\Filter\Null'/*, 'options' => array('type' => 'string')*/),
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
        'string_length_max_2'     => array('name' => 'string_length', 'options' => array('max' => 2)),
        'string_length_max_7'     => array('name' => 'string_length', 'options' => array('max' => 7)),
        'string_length_max_8'     => array('name' => 'string_length', 'options' => array('max' => 8)),
        'string_length_max_16'    => array('name' => 'string_length', 'options' => array('max' => 16)),
        'string_length_max_32'    => array('name' => 'string_length', 'options' => array('max' => 32)),
        'string_length_max_45'    => array('name' => 'string_length', 'options' => array('max' => 45)),
        'string_length_max_64'    => array('name' => 'string_length', 'options' => array('max' => 64)),
        'string_length_max_128'   => array('name' => 'string_length', 'options' => array('max' => 128)),
        'string_length_max_254'   => array('name' => 'string_length', 'options' => array('max' => 254)),
        'string_length_max_255'   => array('name' => 'string_length', 'options' => array('max' => 255)),
        'string_length_max_256'   => array('name' => 'string_length', 'options' => array('max' => 256)),
        'string_length_max_400'   => array('name' => 'string_length', 'options' => array('max' => 400)),
        'string_length_max_512'   => array('name' => 'string_length', 'options' => array('max' => 512)),
        'string_length_max_2048'  => array('name' => 'string_length', 'options' => array('max' => 2048)),
        'string_length_max_16384' => array('name' => 'string_length', 'options' => array('max' => 16384)),
        'string_length_min_4'     => array('name' => 'string_length', 'options' => array('min' => 4))
    );

    /**
     * Returns validation rules.
     * This specifications are an array compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     * Must be defined in each fieldset
     *
     * @see InputFilter\InputFilterProviderInterface
     */
    abstract public function getInputFilterSpecification();

    /**
     * Constructor.
     *
     * @param string $name
     *         Optional name for the element
     * @param array $options
     *         Optional options for the element
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct($name = null, $options = array(), \Doctrine\ORM\EntityManager $em = null) {

        parent::__construct($name, $options);

        $this->setEm($em);

        $translator = I18n\Translator\Translator::Instance();
        $dateFormatter = DateFormatter\DateFormatter::getInstance();

        // set locale, filters & validators
        $this->_locale = $translator->getTranslator()->getTranslator()->getLocale();
        $this->_filters['float']['options']['locale']   = $this->_locale;
        $this->_validators['date']['options']['locale'] = $this->_locale;
        $this->_validators['date']['options']['format'] = $dateFormatter->getFormat($this->_locale);

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
     * @param \Doctrine\ORM\EntityManager $_em
     * @return \Ffb\Backend\Form\AbstractBackendForm
     */
    public function setEm(\Doctrine\ORM\EntityManager $_em = null) {
        $this->_em = $_em;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getLocale() {
        return $this->_locale;
    }

    /**
     * Get array with element validators
     *
     * @param string $name
     * @return array
     * @throws \Exception If not element in form
     */
    public function getFieldValidators($name) {

        if (!$this->has($name)) {
            throw new \Exception('there is no element in fieldset');
        }

        $valNames      = array();
        $specification = $this->getInputFilterSpecification();
        if (!array_key_exists($name, $specification) ||
            array_key_exists($name['validators'], $specification[$name])) {
            return $valNames;
        }

        foreach ($specification[$name]['validators'] as $validator) {
            $valNames[$validator['name']] = $validator['options'];
        }
        return $valNames;
    }
}