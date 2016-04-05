<?php

namespace Ffb\Backend\Model;

use Ffb\Backend\Entity;

use Zend\ServiceManager;

/**
 * @author erdal.mersinlioglu
 */
class TemplateModel extends AbstractBaseModel {

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param \Zend\Log\Logger $logger
     * @param Entity\UserEntity $identity
     */
    public function __construct(
            ServiceManager\ServiceLocatorInterface $sl,
            \Zend\Log\Logger $logger = null,
            Entity\UserEntity $identity = null) {
        $this->_entityClass = 'Ffb\Backend\Entity\TemplateEntity';
        parent::__construct($sl, $logger, $identity);
    }

//    /**
//     * @param $template
//     *
//     * @return array
//     */
//    public function getCategoryTree($template) {
//
//        $categoryModel = new CategoryModel($this->_sl, $this->_logger);
//
//        // prepare category items
//        $items = array();
//        foreach ($categoryModel->getFirstLevelProductCategories() as $category) {
//            $items[] = $this->getCategoryData($category, $template);
//        }
//
//        return $items;
//    }
//
//
//    /**
//     * @param Entity\CategoryEntity $category
//     * @param Entity\TemplateEntity $template
//     *
//     * @return mixed
//     */
//    public function getCategoryData(Entity\CategoryEntity $category, Entity\TemplateEntity $template) {
//
//        // entities
//        $trans            = $category->getCurrentTranslation();
//        $subCategories    = $category->getChilderen();
//        $hasSubCategories = count($subCategories) > 0;
//        $url              = $this->_sl->get('ViewHelperManager')->get('url');
//        $result           = array();
//
//        // checkboxes
//        $checkboxHelper = new \Zend\Form\View\Helper\FormCheckbox();
//
//        $checkbox = new \Zend\Form\Element\Checkbox('assigned');
//        $checkbox->setCheckedValue(1);
//        $checkbox->setUncheckedValue(0);
//
//        if ($category->getTemplate()) {
//
//            $isAssigned = $category->getTemplate()->getId() === $template->getId();
//            $checkbox->setValue($isAssigned);
//        } else {
//            $checkbox->setValue(0);
//        }
//
//        $dataHref = $url('home/default', array(
//            'controller' => 'template',
//            'action'     => 'categoryAssignment',
//            'param'      => 'template',
//            'value'      => $template->getId(),
//            'param2'     => 'category',
//            'value2'     => $category->getId()
//        ));
//        $checkbox->setAttribute('data-href', $dataHref);
//
//        // result
//        $result['title']       = $trans->getName();
//        $result['checkbox']    = $checkboxHelper->render($checkbox);
//        $result['subitems']    = array();
//        $result['hasSubitems'] = $hasSubCategories;
//        if ($hasSubCategories) {
//            foreach ($subCategories as $subCategory) {
//                $result['subitems'][] = $this->getCategoryData($subCategory, $template);
//            }
//        }
//
//        return $result;
//    }

}