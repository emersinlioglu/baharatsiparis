<?php

namespace Ffb\Common\View\Helper;

/**
 * Helper for html link
 */
class HtmlLabelHelper extends \Zend\Form\View\Helper\FormLabel {

    /**
     * Generates a label html by invoke
     *
     * @param  ElementInterface $element
     * @param  $form
     * @param  null|string      $labelContent
     * @param  string           $position
     * @throws Exception\DomainException
     * @return string|FormLabel
     */
    public function __invoke(\Zend\Form\ElementInterface $element, $form, $labelContent = null, $position = null) {

        // invoke parent and get form label
        $originalformLabel = parent::__invoke($element, $labelContent, $position);

        if ($form instanceof \Zend\Form\Form) {

            // check if element is required
            foreach ($form->getFieldValidators($element->getName()) as $name => $validator) {

                if ($name === 'Zend\Validator\NotEmpty') {
                    return str_replace('</label>', '<i>*</i></label>', $originalformLabel);
                }
            }

        } else if ($form instanceof \Zend\Form\FieldsetInterface) {

            // get name after prepare()
            $match       = array();
            preg_match('/\[([a-zA-Z0-9-_]*)\]$/', $element->getName(), $match);

            if (count($match) === 2) {
                $elementName = array_pop($match);
            } else {
                $elementName = $element->getName();
            }

            // check if element is required
            foreach ($form->getFieldValidators($elementName) as $name => $validator) {

                if ($name === 'NotEmpty') {
                    return str_replace('</label>', '<i>*</i></label>', $originalformLabel);
                }
            }
        }

        // not start to optional elements
        return  $originalformLabel;
    }
}
