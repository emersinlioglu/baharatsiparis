<?php

namespace Ffb\Backend\Service;

use Ffb\Backend\Model;
use Ffb\Backend\Entity;
use Ffb\Backend\Form;
use Zend\Json\Json;
use Zend\I18n\Validator;
use Zend\View\Model as ZendModel;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Service for creating or updating a Product
 *
 * @author erdal.mersinlioglu
 */
class ProductService extends AbstractService {

    /**
     * Master language code
     * @var string
     */
    protected $_masterLang = '';

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param \Ffb\Backend\Entity\UserEntity $user (optional)
     *         user who uses this service as entity
     */
    public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $sl, \Ffb\Backend\Entity\UserEntity $user = null) {
        parent::__construct($sl, $user);

        $moduleConf = $this->_sl->get('Config');

        // masterLang
        $this->_masterLang = $moduleConf['translator']['master_language_code'];
    }

    /**
     * Prepares the product form
     *
     * @param array $urlParams
     * @param array $postData
     * @return \Zend\View\Model\ViewModel
     */
    public function form(array $urlParams, array $postData = null) {

        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        $cateogryId  = $urlParams['categoryId'];
        $productId   = $urlParams['productId'];
        $parentId    = $urlParams['parentId'];

        // get model(s)
        $productModel           = new Model\ProductModel($this->getServiceLocator(), $this->_logger, $this->_user);
        $categoryModel          = new Model\CategoryModel($this->getServiceLocator(), $this->_logger, $this->_user);
        $attributeValueModel    = new Model\AttributeValueModel($this->getServiceLocator(), $this->_logger, $this->_user);

        // get entities
        /* @var $product Entity\ProductEntity */
        $product        = $productModel->findById($productId);
        $category       = $categoryModel->findById($cateogryId);
        $parentProduct  = $productModel->findById($parentId);
        if (!$parentProduct && !$category && $product && $product->getId()) {
            $category = $productModel->getProductCategory($product);
        }
        if ($parentProduct) {
            $category = $productModel->getProductCategory($parentProduct);
        }

        // forms
        $form = $this->_getForm($product, $category, $parentProduct);

        // Product Template Attributes
        $attributeGroups = $productModel->getAttributeGroupsData($product, $category);
        $view->setVariable('attributeGroups', $attributeGroups);

        // check post
        if (!is_null($postData)) {

            // assign to form, to validate
            $form->setData($postData);

            if ($form->isValid()) {

                $product = $form->getData();

                // validate attributeValues
                $this->_validateAttributeValues($form);

                if ($product->getId()) {

                    // update
                    $productModel->update($product);
                } else {
                    // insert product
                    $productModel->insert($product);

                    // assign the product as default-product to category
                    if (isset($postData['isRoot']) && $postData['isRoot'] == 1 && $category->getId()) {
                        $category->setDefaultProduct($product);
                        $categoryModel->update($category);
                    }
                }

                $this->_flashMessenger->addMessage($this->_translator->translate('MSG_FORM_UPDATED'));

                // urls to refresh
                $view->setVariable('subnaviUrl', $this->_url->fromRoute('home/default', array(
                    'controller' => 'product',
                    'action'     => 'subnavi'
                )));
                $view->setVariable('productUrl', $this->_url->fromRoute('home/default', array(
                    'controller' => 'product',
                    'action'     => 'productvariants',
                    'param'      => 'product',
                    'value'      => $product->getParent() ? $product->getParent()->getId() : $product->getId()
                )));
                if ($product->getParent()) {
                    $view->setVariable('productVariantUrl', $this->_url->fromRoute('home/default', array(
                        'controller' => 'product',
                        'action'     => 'form',
                        'param'      => 'product',
                        'value'      => $product->getId()
                    )));
                }

            } else {
                $this->_flashMessenger->addMessage($this->_translator->translate('MSG_FORM_INVALID'));
                $view->setVariable('state', 'error');
                $view->setVariable('invalidFields', $form->getInvalidFields());
            }
        } else {

            // parent categorie tree
            if ($category) {
                $categories = $categoryModel->getRootCategoriesTree($category);
                $view->setVariable('categories', $categories);
            }

            if ($product && $product->getId()) {

                if ($product->getIsSystem()) {
                    // linked products
                    $view->setVariable('linkedProductList', $this->getAssignedProductsList($product, Entity\ProductEntity::TYPE_LINKED_PRODUCT));
                }

                // accessory products
                $view->setVariable('accessoryProductsList', $this->getAssignedProductsList($product, Entity\ProductEntity::TYPE_ACCESSORY_PRODUCT));

                $view->setVariables(array(
                    'multipleUsageCategoryTree' => $productModel->getCategoryTreeForMultipleUsage($product),
                    'showLog' => true,
                    'product' => $product,
                    'selectHtml' => $this->_getTimePeriodSelect($product),
                    'attributeValueLog' => $product ? $this->getAttributeValueLogData($product, array()) : array(),
                    // search product url
                    'dataSearchProductUrl' => $this->_url->fromRoute('home/default', array(
                        'controller' => 'product',
                        'action' => 'searchProducts',
                        'param' => 'product',
                        'value' => $product->getId()
                    )),
                    // add linkedProduct url
                    'dataAddLinkedProductUrl' => $this->_url->fromRoute('home/default', array(
                        'controller' => 'product',
                        'action' => 'addAssignedProduct',
                        'param' => 'product',
                        'value' => $product->getId(),
                        'param2' => 'linkedProduct',
                        'value2' => ''
                    )),
                    // add accessoryProduct url
                    'dataAddAccessoryProductUrl' => $this->_url->fromRoute('home/default', array(
                        'controller' => 'product',
                        'action' => 'addAssignedProduct',
                        'param' => 'product',
                        'value' => $product->getId(),
                        'param2' => 'accessoryProduct',
                        'value2' => ''
                    ))
                ));
            }

            $view->setVariables(array(
                'form' => $form,
                'hasParent' => $product && $product->getParent() ? true : false
            ));
        }

        return $view;
    }

    /**
     * Timeperiod Dropdown html for attribute value log
     *
     * @param \Ffb\Backend\Entity\ProductEntity $product
     * @return string
     */
    private function _getTimePeriodSelect(Entity\ProductEntity $product) {

        $selectRenderer = new \Zend\Form\View\Helper\FormSelect();
        $select = new \Zend\Form\Element\Select();
        $select->setName('timeperiod');
        $select->setValueOptions(array(
            'lastDay'  => 'Letzten Tag',
            'lastWeek' => 'Letzte Woche',
            'lastYear' => 'Letztes Jahr'
        ));
        $select->setAttribute('data-url', $this->_url->fromRoute('home/default', array(
            'controller' => 'product',
            'action' => 'log',
            'param' => 'product',
            'value' => $product->getId()
        )));

        return $selectRenderer->render($select);
    }

    /**
     * Prepares product form
     * @param Entity\ProductEntity|null $product
     * @param Entity\CategoryEntity|null $category
     * @param Entity\ProductEntity|null $parentProduct
     * @return Form\ProductForm
     * @throws \Exception
     */
    protected function _getForm(
        Entity\ProductEntity $product = null,
        Entity\CategoryEntity $category = null,
        Entity\ProductEntity $parentProduct = null
    ) {

        // get model(s)
        $productModel        = new Model\ProductModel($this->getServiceLocator(), $this->_logger, $this->_user);
        $attributeModel      = new Model\AttributeModel($this->getServiceLocator(), $this->_logger, $this->_user);
        $attributeValueModel = new Model\AttributeValueModel($this->getServiceLocator(), $this->_logger, $this->_user);

        $urlParams = array(
            'controller' => 'product',
            'action'     => 'form',
            'param'      => 'product',
            'value'      => '0',
            'param2'     => 'category',
            'value2'     => '0'
        );
        if ($category) {
            $urlParams['value2'] = $category->getId();
        }

        if ($product) {
            //attribute exist, get data
            $urlParams['value'] = $product->getId();
        } else {
            // create new attribute
            $product = new Entity\ProductEntity();

            // add AttributeGroupAttribute, if attributeGroupId is given
            if ($category) {
                $productCategory = new Entity\ProductCategoryEntity();
                $productCategory->setCategory($category);
                $product->addProductCategories(new ArrayCollection(array($productCategory)));
            } else {
                throw new \Exception($this->_translator->translate('MSG_MISSING_CATEGORY'));
            }
        }

        // prepare form
        $form = new Form\ProductForm('form-product', array(), $productModel->getEntityManager());
        $form->setAttribute('action', $this->_url->fromRoute('home/default', $urlParams));

        // createMissingTranslations
        $productModel->createMissingTranslations($product, 'Ffb\Backend\Entity\ProductLangEntity');

        // createMissingAttributeValues and persist.
        if ($product->getId()) {
            // attributeValues must be persisted
            // so that the form can work with atrribute value ids
            //$productModel->createMissingAttributeValues($product/*, $category*/);

            // update
            //$productModel->update($product);
        }

        // bind and prepare
        $form->bind($product);
        $form->prepare();

//        // parent value options
//        $parentValueOptions = $productModel->getParentValueOptions($product/*, $category*/);
//        $form->get('parent')->setValueOptions($parentValueOptions);
//        if ($parentProduct) {
//            $form->get('parent')->setValue($parentProduct->getId());
//        }

        // get images for attributes
//        if ($product->getId()) {
//
//            $parent = $product->getParent();
//
//            // get root product
//            $rootProduct = null;
//            if ($productCategory = $product->getProductCategories()->first()) {
//                $pCategory = $productCategory->getCategory();
//                if ($pCategory && $pCategory->getDefaultProduct()) {
//                    $rootProduct = $pCategory->getDefaultProduct();
//                }
//            }
//
//            // set images and documents
//            foreach ($form->get('translations') as $productLangFs) {
//                foreach ($productLangFs->get('attributeValues') as $attributeValueFs) {
//
//                    //{*$productLangFs         = $productLangFs*}
//                    //{*$attributeValueFs      = $attributeValueFs*}
//                    //{$attributeGroupFs      = $attributeValueFs->get('attributeGroup')}
//                    //{$attributeLangFs       = $attributeValueFs->get('attributeLang')}
//                    //{$attributeFs           = $attributeLangFs->get('translationTarget')}
//                    $attributeLangFs  = $attributeValueFs->get('attributeLang');
//                    $attributeGroupFs = $attributeValueFs->get('attributeGroup');
//                    $attributeFs      = $attributeLangFs->get('translationTarget');
//
//                    $attributeGroupId    = $attributeGroupFs->get('id')->getValue();
//                    $attributeLangId     = $attributeLangFs->get('id')->getValue();
//                    $langId              = $attributeLangFs->get('lang')->getValue();
//                    $attributeId         = $attributeFs->get('id')->getValue();
//
//                    /* @var $attribute Entity\AttributeEntity */
//                    $attribute = $attributeModel->findById($attributeId);
//
//                    // set form element params
//                    $this->_setProductFormElementParams(
//                        $attributeValueFs,
//                        $attribute,
//                        $langId
//                    );
//
//                    // set file upload params
//                    $this->_setProductFormFileParams(
//                        $attributeValueFs,
//                        $attribute,
//                        $attributeValueModel
//                    );
//
//                    // set parent attribute values
//                    $this->_setProductFormParentValues(
//                        $product,
//                        $rootProduct,
//                        $attribute,
//                        $attributeValueFs,
//                        $attributeValueModel,
//                        array(
//                            'attributeGroupId' => $attributeGroupId,
//                            'attributeLangId'  => $attributeLangId,
//                            'langId'           => $langId
//                        )
//                    );
//
//                }
//            }
//
//        }

        return $form;
    }

    /**
     * Form element params
     *
     * @param Form\Fieldset\AvAttributeValueFieldset $attributeValueFs
     * @param Entity\AttributeEntity $attribute
     * @param int $langId
     */
    private function _setProductFormElementParams($attributeValueFs, Entity\AttributeEntity $attribute, $langId) {

        $value    = $attributeValueFs->get('value');
        $valueMin = $attributeValueFs->get('valueMin');
        $valueMax = $attributeValueFs->get('valueMax');

        // custom params
        $attributeSettings = array(
            'type'            => $attribute->getType(),
            'isInherited'     => $attributeValueFs->get('isInherited')->getValue(),
            //'attributeEntity' => $attribute
        );
        $value->setAttributes($attributeSettings);
        $valueMin->setAttributes($attributeSettings);
        $valueMax->setAttributes($attributeSettings);

        // label
        $label = $attribute->getCurrentTranslation($langId)->getName();
        $value->setLabel($label);
        $valueMin->setLabel($label);
        $valueMax->setLabel('');

        // label options
        $labelInMasterLang = $attribute->getCurrentTranslation($this->_masterLang)->getName();
        $labelOptions = array(
            //'disable_html_escape' => true
            'labelInMasterLang' => $labelInMasterLang
        );
        $value->setLabelOptions($labelOptions);
        $valueMin->setLabelOptions($labelOptions);

        // type specific params
        switch ($attribute->getType()) {
            case Entity\AttributeEntity::TYPE_VARCHAR:
            case Entity\AttributeEntity::TYPE_TEXT:
            case Entity\AttributeEntity::TYPE_INT:
            case Entity\AttributeEntity::TYPE_FLOAT:
            case Entity\AttributeEntity::TYPE_BOOL:

                break;
            case Entity\AttributeEntity::TYPE_IMAGE:
            case Entity\AttributeEntity::TYPE_DOCUMENT:

                break;
            case Entity\AttributeEntity::TYPE_RANGE_FLOAT:
            case Entity\AttributeEntity::TYPE_RANGE_INT:

                break;
            case Entity\AttributeEntity::TYPE_SELECT:

                // is multiple
                if ($attribute->getIsMultiSelect()) {
                    $value->setAttribute('multiple', 'multiple');
                }

                // option values
                $optionValues  = Model\AttributeValueModel::parseOptionValues($attribute->getOptionValues());
                $valueOptions = array();
                foreach ($optionValues as $key => $opt) {
                    $valueOptions[$opt] = $opt;
                }
                $value->setOption('empty_option', $this->_translator->translate('OPT_EMPTY'));
                $value->setOption('value_options', $valueOptions);

                if ($attribute->getIsMultiSelect()) {
                    $parsedValue = Model\AttributeValueModel::parseOptionValues($value->getValue());
                } else {
                    $parsedValue = trim($value->getValue(), '"');
                }
                $value->setValue($parsedValue);

                break;
            default:
                break;
        }

    }

    /**
     * Sets the parent attribute values params in the form
     *
     * @param \Ffb\Backend\Entity\ProductEntity $product
     * @param \Ffb\Backend\Form\Fieldset\AvAttributeValueFieldset $attributeValueFs
     * @param \Ffb\Backend\Model\AttributeValueModel $attributeValueModel
     * @param \Ffb\Backend\Entity\ProductEntity $rootProduct
     * @param Entity\AttributeEntity $attribute
     * @param array $data
     */
    private function _setProductFormParentValues(
        Entity\ProductEntity $product,
        Entity\ProductEntity $rootProduct = null,
        Entity\AttributeEntity $attribute,
        Form\Fieldset\AvAttributeValueFieldset $attributeValueFs,
        Model\AttributeValueModel $attributeValueModel,
        array $data
    ) {

        // parent product
        $parent = $product->getParent();

        // attributes for the actual translation
        $attributeGroupId = $data['attributeGroupId'];
        $attributeLangId  = $data['attributeLangId'];
        $langId           = $data['langId'];

        // form elements
        $valueElement    = $attributeValueFs->get('value');
        $valueMinElement = $attributeValueFs->get('valueMin');
        $valueMaxElement = $attributeValueFs->get('valueMax');

        // inherited values
        $pValue = $pValueMin = $pValueMax = '';

        // root product attributeValue
        $rootProductAttributeValue = $attributeValueModel->findParentAttributeValue(
            $rootProduct,
            $attributeGroupId,
            $attributeLangId,
            $langId
        );
        if ($rootProductAttributeValue) {
            $pValue    = $rootProductAttributeValue->getValue();
            $pValueMin = $rootProductAttributeValue->getValueMin();
            $pValueMax = $rootProductAttributeValue->getValueMax();
        }

        // parent product attributeValue
        $pAttributeValue = null;
        if ($parent) {
            $pAttributeValue = $attributeValueModel->findParentAttributeValue(
                $parent,
                $attributeGroupId,
                $attributeLangId,
                $langId
            );
            if ($pAttributeValue && !$pAttributeValue->getIsInherited()) {
                $pValue    = $pAttributeValue->getValue();
                $pValueMin = $pAttributeValue->getValueMin();
                $pValueMax = $pAttributeValue->getValueMax();
            }
        }

        switch ($attribute->getType()) {
            case Entity\AttributeEntity::TYPE_VARCHAR:
            case Entity\AttributeEntity::TYPE_TEXT:
            case Entity\AttributeEntity::TYPE_INT:
            case Entity\AttributeEntity::TYPE_FLOAT:
            case Entity\AttributeEntity::TYPE_BOOL:

                $attributeValueFs->get('value')->setAttribute('parentValue', $pValue);
                break;

            case Entity\AttributeEntity::TYPE_IMAGE:
            case Entity\AttributeEntity::TYPE_DOCUMENT:

                // get uploaded files
                $files = array();
                $attributeValueId = null;
                if ($pAttributeValue && !$pAttributeValue->getIsInherited()) {
                    $attributeValueId = $pAttributeValue->getId();
                } else if ($rootProductAttributeValue) {
                    $attributeValueId = $rootProductAttributeValue->getId();
                }

                // determine destination by attribute type
                $destination  = null;
                if ($attributeValueId) {
                    switch ($attribute->getType()) {
                        case Entity\AttributeEntity::TYPE_IMAGE:
                            $destination = 'image';
                            break;
                        case Entity\AttributeEntity::TYPE_DOCUMENT:
                            $destination = 'file';
                            break;
                        default:
                            break;
                    }
                    $files = $attributeValueModel->getFilesForUploadInput($attributeValueId, $destination);
                }

                $valueElement->setAttribute('parentValue', Json::encode(array(
                    'files' => $files,
                    'type' => $destination
                )));
                break;

            case Entity\AttributeEntity::TYPE_RANGE_FLOAT:
            case Entity\AttributeEntity::TYPE_RANGE_INT:
                $valueMinElement->setAttribute('parentValue', $pValueMin);
                $valueMaxElement->setAttribute('parentValue', $pValueMax);
                break;

            case Entity\AttributeEntity::TYPE_SELECT:

                $parsedValue = Model\AttributeValueModel::parseOptionValues($pValue);
                //if ($attribute->getIsMultiSelect()) {
                //    $parsedValue = Model\AttributeValueModel::parseOptionValues($pValue);
                //} else {
                //    $parsedValue = trim($pValue, '"');
                //}

                $valueElement->setAttribute('parentValue', $parsedValue);
                break;

            default:
                break;
        }

    }

    /**
     * Sets the json array for the file upload elements
     *
     * @param Form\Fieldset\AvAttributeValueFieldset $attributeValueFs
     * @param Entity\AttributeEntity $attribute
     * @param Model\AttributeValueModel $attributeValueModel
     * @return voide
     */
    private function _setProductFormFileParams($attributeValueFs, $attribute, $attributeValueModel) {

        if (!in_array($attribute->getType(), array(
                Entity\AttributeEntity::TYPE_IMAGE,
                Entity\AttributeEntity::TYPE_DOCUMENT
            ))) {
            //continue;
            return;
        }

        $attributeValueId = $attributeValueFs->get('id')->getValue();

        // set referenceType
        $attributeValueFs->get('referenceType')->setValue(Entity\AttributeValueEntity::FILE_REF);

        // determine uploadType && destination by attribute type
        $uploadType = $destination = null;
        switch ($attribute->getType()) {
            case Entity\AttributeEntity::TYPE_IMAGE:
                $uploadType   = 'images';
                $destination = 'image';
                break;
            case Entity\AttributeEntity::TYPE_DOCUMENT:
                $uploadType   = 'documents';
                $destination = 'file';
                break;
            default:
                $uploadType   = 'documents';
                $destination  = 'file';
                break;
        }

        // get uploaded files
        $files = $attributeValueModel->getFilesForUploadInput($attributeValueId, $destination);

        // set upload values
        $attributeValueFs->get('value')->setValue(Json::encode(array(
            'type'               => $uploadType,
            'destination'        => $destination,
            'referenceTypeInput' => $attributeValueFs->get('referenceType')->getName(),
            'idInput'            => $attributeValueFs->get('id')->getName(),
            'destinationInput'   => 'destionationInput',
            'uploadFormUrl'      => $this->_url->fromRoute('home/default', array('controller' => 'upload','action' => 'index')),
            'uploadFileUrl'      => $this->_url->fromRoute('home/default', array('controller' => 'upload','action' => 'upload')),
            'deleteFileUrl'      => $this->_url->fromRoute('home/default', array('controller' => 'upload','action' => 'delete')),
            'updateFileUrl'      => $this->_url->fromRoute('home/default', array('controller' => 'upload','action' => 'update')),
            'files'              => $files
        )));
    }

    /**
     * Validate attributeValues of a product
     *
     * @param \Zend\Form\Form $form
     * @return boolean
     * @throws \Exception
     */
    private function _validateAttributeValues(\Zend\Form\Form $form) {

        // get validation errors
        $validationErrors = $this->_validateValues($form);

        // check validationErrors
        if (!empty($validationErrors)) {

            // convert errors to string
            $messageString = $this->_getValidationErrorsMessage($validationErrors);

            // throw exception
            throw new \Exception($messageString);
        }

        return true;
    }

    /**
     * Returns the validation errors as array
     *
     * $result = array(
     *     'langId' => array(
     *         'attributeGroupId' => array(
     *              'attributeId' => array(
     *                  'errorMessage'
     *              )
     *          )
     *      )
     * )
     *
     * @param \Zend\Form\Form $form
     * @return array
     */
    private function _validateValues(\Zend\Form\Form $form)  {

        // validationErrors
        $validationErrors = array();
        $floatValidator = new \Zend\I18n\Validator\Float();
        $floatValidator->setMessage("The input '%value%' does not appear to be a float.", Validator\Float::NOT_FLOAT);
        $floatValidator->setMessage("'%value%' Invalid type given. String, integer or float expected.", Validator\Float::INVALID);
        $floatValidator->setLocale($this->_translator->getLocale());

        $intValidator   = new \Zend\I18n\Validator\Int();
        $intValidator->setLocale($this->_translator->getLocale());

        // validate attributeValues
        foreach ($form->get('translations') as $productLangFs) {

            $langId = $productLangFs->get('lang')->getValue();

            foreach ($productLangFs->get('attributeValues') as $attributeValueFs) {

                // check if value is set
                $value         = $attributeValueFs->get('value')->getValue();
                $valueMin      = $attributeValueFs->get('valueMin')->getValue();
                $valueMax      = $attributeValueFs->get('valueMax')->getValue();

                // fieldsets
                $attributeLangFs  = $attributeValueFs->get('attributeLang');
                $attributeGroupFs = $attributeValueFs->get('attributeGroup');
                $attributeFs      = $attributeLangFs->get('translationTarget');

                // attributeType
                $attributeId        = $attributeFs->get('id')->getValue();
                $attributeType      = $attributeFs->get('type')->getValue();
                $attributeGroupId   = $attributeGroupFs->get('id')->getValue();

                $errorMessages = array();
                switch ($attributeType) {
                    case Entity\AttributeEntity::TYPE_VARCHAR:

                        break;
                    case Entity\AttributeEntity::TYPE_TEXT:

                        break;
                    case Entity\AttributeEntity::TYPE_INT:
                        if (!empty($value) && !$intValidator->isValid($value)) {
                            $errorMessages = $intValidator->getMessages();
                        }
                        break;
                    case Entity\AttributeEntity::TYPE_FLOAT:
                        if (!empty($value) && !$floatValidator->isValid($value)) {
                            $errorMessages = $floatValidator->getMessages();
                        }
                        break;
                    case Entity\AttributeEntity::TYPE_BOOL:

                        break;
                    case Entity\AttributeEntity::TYPE_RANGE_INT:
                        $errorMessages = $this->_validateRangeValue($intValidator, $valueMin, $valueMax);
                        break;
                    case Entity\AttributeEntity::TYPE_RANGE_FLOAT:
                        $errorMessages = $this->_validateRangeValue($floatValidator, $valueMin, $valueMax);
                        break;
                    case Entity\AttributeEntity::TYPE_IMAGE:

                        break;
                    case Entity\AttributeEntity::TYPE_DOCUMENT:

                        break;
                    case Entity\AttributeEntity::TYPE_SELECT:

                        break;
                    default:
                        break;
                }

                if (!empty($errorMessages)) {
                    $validationErrors[$langId][$attributeGroupId][$attributeId] = $errorMessages;
                }

            }
        }

        return $validationErrors;
    }

    /**
     * Validates range values
     *
     * @param type $validator
     * @param string $valueMin
     * @param string $valueMax
     * @return array
     */
    private function _validateRangeValue($validator, $valueMin, $valueMax) {
        $errorMessages = array();

        // valueMin
        if (!empty($valueMin) && !$validator->isValid($valueMin)) {
            $errorMessages = array_merge($errorMessages, array_values($validator->getMessages()));
        }

        // valueMax
        if (!empty($valueMax) && !$validator->isValid($valueMax)) {
            $errorMessages = array_merge_recursive($errorMessages, array_values($validator->getMessages()));
        }

        // greater than
        if (!empty($valueMin) && !empty($valueMax) && $valueMin >= $valueMax) {
            $errorMessages[] = $this->_translator->translate('MSG_SECOND_VALUE_MUST_BE_GREATER');
        }

        if (empty($valueMin) && !empty($valueMax)) {
            $errorMessages[] = $this->_translator->translate('MSG_FIRST_VALUE_MUST_BE_ENTERED');
        }

        return $errorMessages;
    }

    /**
     * Returns validations errors as string
     *
     * @param array $validationErrors
     * @return string
     */
    private function _getValidationErrorsMessage($validationErrors) {

        $langModel = new Model\LangModel($this->getServiceLocator(), $this->_logger);
        $languages = $langModel->getActiveLanguagesAsArray();

        $attributeGroupModel = new Model\AttributeGroupModel($this->getServiceLocator(), $this->_logger);
        $attributeModel      = new Model\AttributeModel($this->getServiceLocator(), $this->_logger);
        $attributeGroups = array();
        $attributes = array();

        $messageString = '';
        foreach ($validationErrors as $langId => $attributeGroupsData) {

            $messageString .= '[' .$languages[$langId] . ']<br>';

            foreach ($attributeGroupsData as $attributeGroupId => $attributesData) {

                // attributeGroup
                if (!isset($attributeGroups[$attributeGroupId])) {
                    $attributeGroups[$attributeGroupId] = $attributeGroupModel->findById($attributeGroupId);
                }
                $attributeGroup = $attributeGroups[$attributeGroupId];

                // attributeGroup Message
                $messageString .= ' == ' . $attributeGroup->getCurrentTranslation()->getName() . ' ==<br>';

                // attributes Messages
                foreach ($attributesData as $attributeId => $messages) {

                    // attributeGroup
                    if (!isset($attributes[$attributeId])) {
                        $attributes[$attributeId] = $attributeModel->findById($attributeId);
                    }
                    $attribute = $attributes[$attributeId];
                    $attributeName = $attribute->getCurrentTranslation()->getName();

                    $messageString .= '* ' . $attributeName . ': <br>--- ' . implode('<br>--- ', $messages) . '<br>';
                }
            }
        }

        return $messageString;
    }

    /**
     * Returns attribute value log data as array
     *
     * @param \Ffb\Backend\Entity\ProductEntity $product
     * @param array $data
     * @return array
     */
    public function getAttributeValueLogData(Entity\ProductEntity $product, $data = null) {

        $attributeValueLogModel = new Model\AttributeValueLogModel($this->getServiceLocator(), $this->_logger, $this->_user);
        $result = array();

        // filter
        $offset = isset($data['offset']) ? (int)$data['offset'] : 0;
        $limit  = 10;
        $filter = array(
            'timeperiod' => 'lastweek',
            'productId' => $product->getId()
        );
        if (is_array($data)) {
            $filter = array_merge($filter, $data);
        }

        $avLogs = $attributeValueLogModel->getPageData($offset, $limit, null, null, null, $filter);

        foreach($avLogs as $avLog) {

            $attributeValue = $avLog->getAttributeValue();
            $attributeGroup = $attributeValue->getAttributeGroup();
            $attribute      = $attributeValue->getAttributeLang()->getTranslationTarget();
            $attributeName  = $attribute->getCurrentTranslation()->getName();

            $action = '';
            switch ($avLog->getItem()) {
                case 'is_inherited':
                    $action = "Flag zur Übernahme von Parent-Wert für das Attribute '{$attributeName}'";
                    break;
                default:
                    $action = "Änderung von Attribute '{$attributeName}'";
                    break;
            }

            $result[] = array(
                'date' => $avLog->getDate(),
                'attributeGroup' => $attributeGroup->getCurrentTranslation()->getName(),
                'attribute'      => $attributeName,
                'oldValue'       => $avLog->getOldValue(),
                'newValue'       => $avLog->getNewValue(),
                'action'         => $action,
                'user'           => $avLog->getUser() ? $avLog->getUser()->getName() : $this->_translator->translate('LBL_USER_DOESNT_EXIST'),
                'langIso'        => $attributeValue->getProductLang()->getLang()->getIso(),
                'langId'         => $attributeValue->getProductLang()->getLang()->getId(),
            );
        }

        return $result;
    }

    /**
     * Generates linked list html for linkedProducts
     *
     * @param \Ffb\Backend\Entity\ProductEntity $product
     * @param string $productRelation [ProductEntity::TYPE_LINKED_PRODUCT, ProductEntity::TYPE_ACCESSORY_PRODUCT]
     *
     * @return string
     */
    public function getAssignedProductsList(Entity\ProductEntity $product, $productRelation) {

        $listHelper = new \Ffb\Backend\View\Helper\HtmlLinkedListHelper();

        $assignedProducts = array();
        if ($productRelation == Entity\ProductEntity::TYPE_LINKED_PRODUCT) {
            $assignedProducts = $product->getLinkedProducts();
        } else if ($productRelation == Entity\ProductEntity::TYPE_ACCESSORY_PRODUCT) {
            $assignedProducts = $product->getAccessoryProducts();
        }

        $data = array();
        foreach ($assignedProducts as $assignedProduct) {

            $removeUrl = $this->_url->fromRoute('home/default', array(
                'controller' => 'product',
                'action' => 'removeAssignedProduct',
                'param' => 'product',
                'value' => $product->getId(),
                'param2' => $productRelation,
                'value2' => $assignedProduct->getId()
            ));

            $content = $assignedProduct->getCurrentTranslation()->getName();
            $content .= '<span class="remove" data-remove-url="' . $removeUrl . '"></span>';
            $data[] = $content;
        }

        return $listHelper->getHtml($data, null, null, false);
    }

}