<?php

namespace Ffb\Backend\Model;

use DoctrineORMModule\Proxy\__CG__\Ffb\Backend\Entity\CategoryEntity;
use Ffb\Backend\Entity;

use Ffb\Common\I18n\Translator\Translator;
use Zend\ServiceManager;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author erdal.mersinlioglu
 */
class CategoryModel extends AbstractBaseModel {

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param \Zend\Log\Logger $logger
     * @param Entity\UserEntity $identity
     */
    public function __construct(
            ServiceManager\ServiceLocatorInterface $sl,
            \Zend\Log\Logger $logger = null,
            Entity\UserEntity $identity = null) {
        $this->_entityClass = 'Ffb\Backend\Entity\CategoryEntity';
        parent::__construct($sl, $logger, $identity);
    }

    /**
     * Build an category
     *
     *   $data = array(
     *       'translations' => array(
     *          'de' => $nameDe,
     *          'en' => $nameEn,
     *       )
     *   );
     *
     * @param array $data
     * @return Entity\CategoryEntity
     */
    public function build(array $data) {

        // entity
        $category = new Entity\CategoryEntity();

        if (isset($data['translations'])) {

            // model(s)
            $langModel = new LangModel($this->_sl);

            // create tranlsations
            $translations = new ArrayCollection();
            foreach ($data['translations'] as $langCode => $translationData) {

                // lang
                $lang = $langModel->findOneBy(array('iso' => strtolower($langCode)));
                if (!$lang) {
                    continue;
                }

                // categoryLang
                $categoryLang = new Entity\CategoryLangEntity();
                $categoryLang->setName($translationData['name']);
                $categoryLang->setLang($lang);

                $translations->add($categoryLang);
            }

            // add translations
            $category->addTranslations($translations);
        }

        return $category;

    }

    /**
     * Returns the sub product groups of the $category
     *
     * @param \Ffb\Backend\Entity\ProductGroupEntity $category
     * @return array
     */
    public function getFirstLevelProductCategories() {

        $qb = $this->getRepository()->createQueryBuilder('category');
        $qb->andWhere($qb->expr()->isNull('category.parent'));
        $qb->orderBy('category.sort', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns aggregated category
     *
     * @param \Ffb\Backend\Entity\CategoryEntity $category
     * @return Entity\TemplateEntity
     */
    public function getAggregatedTemplate(Entity\CategoryEntity $category) {
        $template = $category->getTemplate();

        if ($template) {
            return $template;
        } else if (!$template && $category->getParent()) {
            return $this->getAggregatedTemplate($category->getParent());
        }
    }

    /**
     * Finds the root product groups to assign as parent
     *
     * @return array (
     *      'id',
     *      'categoryName',
     *      'parentId'
     * )
     */
    public function findFirstAndSecondLevelCategories() {
        $qb = $this->getRepository()->createQueryBuilder('category');

        $qb->select('category.id as id');
        $qb->addSelect('childeren.id as parentId');
        $qb->leftJoin('category.childeren', 'childeren');
        $qb->andWhere('category.parent IS NULL');

        $result = array();
        $resultHierarchic = array();
        foreach ($qb->getQuery()->getResult() as $row) {
            $firstLevel = $row['id'];
            $secondLevel = $row['parentId'];
            $resultHierarchic[$firstLevel][] = $secondLevel;

            if (!empty($firstLevel)) {
                $result[$firstLevel] = $firstLevel;
            }
            if (!empty($secondLevel)) {
                $result[$secondLevel] = $secondLevel;
            }
        }

        return array(
            'hierarchic' => $resultHierarchic,
            'all' => $result
        );
    }

    public function getCategoryNames(array $ids) {

        $qb = $this->getRepository()->createQueryBuilder('category');

        $qb->select('category.id');
        $qb->addSelect('trans.name');
        $qb->leftJoin('category.translations', 'trans');
        $qb->leftJoin('trans.lang', 'lang');
        $qb->andWhere('category in (:ids)');
        $qb->andWhere('lang.iso = :iso');
        $qb->setParameter('ids', $ids);
        $qb->setParameter('iso', Entity\LangEntity::DEFAULT_LANGUAGE_CODE);

        $result = array();
        foreach ($qb->getQuery()->getResult() as $row) {
            $result[$row['id']] = $row['name'];
        }

        return $result;
    }

    public function getParentsValueOptions() {

        $categories = $this->findFirstAndSecondLevelCategories();

        $categpriesHierarchic = $categories['hierarchic'];
        $categoryNames = $this->getCategoryNames($categories['all']);

        $result = array();
        foreach ($categpriesHierarchic as $firstLevelCategoryId => $secondLevelCategories) {

            $options = array();
            $options[] = array(
                'label' => $categoryNames[$firstLevelCategoryId],
                'value' => $firstLevelCategoryId,
                'attributes' => array('class' => 'first-level')
            );
            foreach ($secondLevelCategories as $secondLevelCategoryId) {
                if (empty($secondLevelCategoryId)) {
                    continue;
                }
                $options[] = array(
                    'label' => $categoryNames[$secondLevelCategoryId],
                    'value' => $secondLevelCategoryId,
                    'attributes' => array('class' => 'second-level')
                );
            }

            $result = array_merge($result, $options);
        }

        return $result;
    }

    /**
     * @param $template
     *
     * @return array
     */
    public function getCategoryTree($template) {

        // prepare category items
        $items = array();
        foreach ($this->getFirstLevelProductCategories() as $category) {
            $items[] = $this->getCategoryData($category, $template);
        }

        return $items;
    }


    /**
     * @param Entity\CategoryEntity $category
     * @param Entity\TemplateEntity $template
     *
     * @return mixed
     */
    public function getCategoryData(Entity\CategoryEntity $category, Entity\TemplateEntity $template) {

        // entities
        $trans            = $category->getCurrentTranslation();
        $subCategories    = $category->getChilderen();
        $hasSubCategories = count($subCategories) > 0;
        $url              = $this->_sl->get('ViewHelperManager')->get('url');
        $result           = array();

        // checkboxes
        $checkboxHelper = new \Zend\Form\View\Helper\FormCheckbox();

        $checkbox = new \Zend\Form\Element\Checkbox('assigned');
        $checkbox->setCheckedValue(1);
        $checkbox->setUncheckedValue(0);

        if ($category->getTemplate()) {

            $isAssigned = $category->getTemplate()->getId() === $template->getId();
            $checkbox->setValue($isAssigned);
        } else {
            $checkbox->setValue(0);
        }

        $dataHref = $url('home/default', array(
            'controller' => 'template',
            'action'     => 'categoryAssignment',
            'param'      => 'template',
            'value'      => $template->getId(),
            'param2'     => 'category',
            'value2'     => $category->getId()
        ));
        $checkbox->setAttribute('data-href', $dataHref);

        // result
        $result['title']       = $trans->getName();
        $result['checkbox']    = $checkboxHelper->render($checkbox);
        $result['subitems']    = array();
        $result['hasSubitems'] = $hasSubCategories;
        if ($hasSubCategories) {
            foreach ($subCategories as $subCategory) {
                $result['subitems'][] = $this->getCategoryData($subCategory, $template);
            }
        }

        return $result;
    }

    /**
     * Returns the root category tree
     *
     * @param Entity\CategoryEntity|null $category
     * @return array
     */
    public function getRootCategoriesTree(Entity\CategoryEntity $category = null, $masterLang) {
        $result = array();
        $langModel = new LangModel($this->_sl, $this->_logger);

        if ($category) {
            do {
                $masterTrans = $category->getCurrentTranslation($masterLang)->getName();
                $translations = array();
                foreach($langModel->getActiveLanguagesAsArray() as $langId => $langCode) {
                    $translations[$langCode] = $category->getCurrentTranslation($langId)->getName();
                }

                $data = array(
                    'masterTrans' => $masterTrans,
                    'translations' => $translations
                );

                array_unshift($result, $data);

            } while($category = $category->getParent());
        }

        return $result;
    }


    /**
     * @return array
     */
    public function getCategorySortTree() {

        // prepare category items
        $items = array();
        foreach ($this->getFirstLevelProductCategories() as $category) {
            $items[] = $this->_getCategorySortData($category);
        }

        return $items;
    }

    private function _getCategorySortData(Entity\CategoryEntity $category) {

        // entities
        $trans            = $category->getCurrentTranslation();
        $subCategories    = $category->getChilderen();
        $hasSubCategories = count($subCategories) > 0;
        $url              = $this->_sl->get('ViewHelperManager')->get('url');
        $result           = array();

        // checkboxes
        $formInput = new \Zend\Form\View\Helper\FormInput();

        $hiddenInput = new \Zend\Form\Element\Hidden("category[{$category->getId()}]");
        $hiddenInput->setValue($category->getSort());

        // result
        $name = $trans->getName();
        if (!$name) {
            $name = Translator::translate('LBL_NO_TRANSLATION');
        }

        $result['title']       = $name;
        $result['id']          = $category->getId();
        $result['hiddenInput'] = $formInput->render($hiddenInput);
        $result['subitems']    = array();
        $result['hasSubitems'] = $hasSubCategories;
        if ($hasSubCategories) {
            foreach ($subCategories as $subCategory) {
                $result['subitems'][] = $this->_getCategorySortData($subCategory);
            }
        }

        return $result;
    }

}