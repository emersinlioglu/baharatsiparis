<?php

namespace Ffb\Frontend\Form;

use Zend\InputFilter;

/**
 * DocumentFieldset: this class is commented cause we don't need the feature
 * to upload multiple files at once. Instead each file will be uploaded through
 * a single Ajax request.
 *
 * @author marcus.gnass
 */
class UploadForm extends \Ffb\Common\Form\AbstractUploadForm {

    /**
     *
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     */
    public function __construct($name = 'form-upload', $options = array()) {
        parent::__construct($name, $options);

    }
}