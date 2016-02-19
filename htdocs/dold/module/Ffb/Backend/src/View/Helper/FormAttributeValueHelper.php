<?php

namespace Ffb\Backend\View\Helper;

use Ffb\Backend\Model\AttributeValueModel;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Select;
use Zend\Form\Element\Text;
use Zend\Form\Element\Textarea;
use \Zend\Form\ElementInterface;
use \Zend\Form\Exception;
use Zend\Form\View\Helper\FormCheckbox;
use Zend\Form\View\Helper\FormHidden;
use Zend\Form\View\Helper\FormInput;
use Zend\Form\View\Helper\FormLabel;
use Zend\Form\View\Helper\FormSelect;
use Ffb\Backend\Entity;
use Zend\Form\View\Helper\FormText;
use Zend\Form\View\Helper\FormTextarea;
use Zend\Json\Json;

/**
 * Class FormAttributeValueHelper
 *
 * 'attributes' => array(
 *      'type' => int
 *      //'isMultiSelect' => 0|1
 *      //'optionValues' => string
 *      'isInherited' => 0|1
 * )
 *
 * @package Ffb\Backend\View\Helper
 */
class FormAttributeValueHelper extends \Zend\Form\View\Helper\AbstractHelper {

    /**
     * Attribute types
     *
     * @var array
     */
    protected $attributeTypes = array(
        Entity\AttributeEntity::TYPE_VARCHAR        => 'LBL_ATTRIBUTE_TYPE_VARCHAR',
        Entity\AttributeEntity::TYPE_TEXT           => 'LBL_ATTRIBUTE_TYPE_TEXT',
        Entity\AttributeEntity::TYPE_INT            => 'LBL_ATTRIBUTE_TYPE_INT',
        Entity\AttributeEntity::TYPE_FLOAT          => 'LBL_ATTRIBUTE_TYPE_FLOAT',
        Entity\AttributeEntity::TYPE_BOOL           => 'LBL_ATTRIBUTE_TYPE_BOOL',
        Entity\AttributeEntity::TYPE_RANGE_INT      => 'LBL_ATTRIBUTE_TYPE_RANGE_INT',
        Entity\AttributeEntity::TYPE_RANGE_FLOAT    => 'LBL_ATTRIBUTE_TYPE_RANGE_FLOAT',
        Entity\AttributeEntity::TYPE_IMAGE          => 'LBL_ATTRIBUTE_TYPE_IMAGE',
        Entity\AttributeEntity::TYPE_DOCUMENT       => 'LBL_ATTRIBUTE_TYPE_DOCUMENT',
        Entity\AttributeEntity::TYPE_SELECT         => 'LBL_ATTRIBUTE_TYPE_SELECT'
    );

    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface|null $element
     * @return string|FormInput
     */
    public function __invoke(ElementInterface $element = null) {

        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }

    /**
     * Render a form <input> element from the provided $element
     *
     * @param  ElementInterface $element
     * @throws Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element) {

        $name = $element->getName();
        if ($name === null || $name === '') {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }

        $labelRenderer  = new FormLabel();
        $renderer       = null;
        $formElement    = null;
        $rowClass       = 'text';

        switch($element->getAttribute('type')) {
            case Entity\AttributeEntity::TYPE_VARCHAR:
            case Entity\AttributeEntity::TYPE_INT:
            case Entity\AttributeEntity::TYPE_FLOAT:
                $renderer       = new FormInput();
                $formElement    = new Text();
                break;
            case Entity\AttributeEntity::TYPE_TEXT:
                $renderer       = new FormTextarea();
                $formElement    = new Textarea();
                $rowClass       = 'textarea';
                break;
            case Entity\AttributeEntity::TYPE_BOOL: ;
                $renderer       = new FormCheckbox();
                $formElement    = new Checkbox();
                $rowClass       = 'checkbox';
                break;
            case Entity\AttributeEntity::TYPE_RANGE_INT:
            case Entity\AttributeEntity::TYPE_RANGE_FLOAT:
                $renderer       = new FormInput();
                $formElement    = new Text();
                break;
            case Entity\AttributeEntity::TYPE_IMAGE:
            case Entity\AttributeEntity::TYPE_DOCUMENT:
                $renderer       = new FormHidden();
                $formElement    = new Hidden();
                $rowClass       = 'fileupload';
                break;
            case Entity\AttributeEntity::TYPE_SELECT:
                $rowClass       = 'select';
                $renderer    = new FormSelect();
                $formElement = new Select();
                break;
            default:
                //$renderer = new FormText();
                break;
        }

        // attributes
        $formElement->setName($element->getName());
        $formElement->setAttributes($element->getAttributes());
        $formElement->setOptions($element->getOptions());
        $formElement->setLabel($this->_getLabel($element));
        $formElement->setValue($element->getValue());

        // render label
        $labelHtml = $labelRenderer($formElement);

        // render value
        $valueHtml = $labelHtml . $renderer($formElement);

        // render parent value
        $parentValueHtml = $labelHtml . $this->_getParentValueHtml($element);

        $showParent = $element->getAttribute('isInherited');
        return $this->_getWrapperHtml($parentValueHtml, $valueHtml, $showParent, $rowClass);
    }

    /**
     * Parent value html
     *
     * @param $element
     * @return string
     */
    private function _getParentValueHtml(ElementInterface $element) {

        $html = '';
        $pValue = $element->getAttribute('parentValue');

        switch($element->getAttribute('type')) {
            case Entity\AttributeEntity::TYPE_VARCHAR:
            case Entity\AttributeEntity::TYPE_INT:
            case Entity\AttributeEntity::TYPE_FLOAT:
                $html = $pValue;
                break;
            case Entity\AttributeEntity::TYPE_TEXT:
                $html = $pValue;
                break;
            case Entity\AttributeEntity::TYPE_BOOL:
                $renderer = new FormCheckbox();
                $checkbox = new Checkbox($element->getName());
                $checkbox->setValue($pValue);
                $checkbox->setAttribute('disabled', 'disabled');
                $html = $renderer($checkbox);
                break;
            case Entity\AttributeEntity::TYPE_RANGE_INT:
                break;
            case Entity\AttributeEntity::TYPE_RANGE_FLOAT:
                break;
            case Entity\AttributeEntity::TYPE_IMAGE:
            case Entity\AttributeEntity::TYPE_DOCUMENT:
                $html = '<div data-files=\'' . $pValue . '\'></div>';
                break;
            case Entity\AttributeEntity::TYPE_SELECT:
                $html = '<ul>';
                foreach ($pValue as $opt) {
                    $html .= "<li>{$opt}</li>";
                }
                $html .= '</ul>';
                break;
            default:
                break;
        }

        return $html;
    }

    /**
     * Returns label
     * @return string
     */
    private function _getLabel($element) {

        // standard label
        $label = $element->getLabel();

        // label in master lang
        $masterTranslation = $element->getLabelOption('labelInMasterLang');
        if (strlen($masterTranslation) > 0) {
            $labelInMasterLang = ' <span class="master-lang">(%s)</span>';
            $labelInMasterLang = sprintf($labelInMasterLang, $masterTranslation);
            $label .= $labelInMasterLang;
        }

        if (strlen($label) == 0) {
            $label = ' ';
        }

        return $label;
    }

    /**
     * Generates element wrapper html
     *
     * @param $parentValueHtml
     * @param $valueHtml
     * @param $showParent
     * @param $rowType
     * @return string
     */
    protected function _getWrapperHtml($parentValueHtml, $valueHtml, $showParent, $rowType) {

        $valueClass = $parentClass = $rowType;
        if ($showParent) {
            $valueClass .= ' hide';
        } else {
            $parentClass .= ' hide';
        }

        $htmlParent = '<div class="row view %s">%s</div>';
        $htmlValue = '<div class="row %s">%s</div>';

        $htmlParent = sprintf($htmlParent, $parentClass, $parentValueHtml);
        $htmlValue  = sprintf($htmlValue, $valueClass, $valueHtml);

        return $htmlParent . $htmlValue;
    }

}

