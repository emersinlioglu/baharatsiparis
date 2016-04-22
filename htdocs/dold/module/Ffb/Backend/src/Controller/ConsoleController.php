<?php

namespace Ffb\Backend\Controller;

use Doctrine\Common\Collections;

use Ffb\Backend\Entity;
use Ffb\Backend\Model;

use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ConsoleModel;

/**
 * Extending from AbstractConsoleController asserts that this controller is
 * called but from console.
 *
 * In order to execute one of the actions
 * assert that you chose the right environement:
 *
 *     e.g. export APPLICATION_ENV=development
 *
 * @author erdal.mersinlioglu
 * @see https://samsonasik.wordpress.com/2014/11/29/zend-framework-2-using-abstractconsolecontroller-and-consolemodel/
 */
class ConsoleController extends AbstractConsoleController {

    /**
     * Attach default listeners
     */
    protected function attachDefaultListeners() {
        parent::attachDefaultListeners();

        $events = $this->getEventManager();
        $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onInit'));
    }

    /**
     * @param MvcEvent $e
     * @return MvcEvent
     */
    public function onInit(MvcEvent $e) {

        // init translator
        $this->_initTranslator();
    }

    /**
     * Check authentication.
     * Do nothing on CLI environment.
     */
    public function checkAuthentication() {}

    /**
     */
    private function _initTranslator() {

        // start session
        $session = new \Zend\Session\Container('default');

        // get config
        $modConf = $this->getServiceLocator()->get('Config');

        // get locale
        //$locale = $modConf['translator']['master_language_code'];
        $locale = $modConf['translator']['locale'];

        // set language in session
        $session->offsetSet('languageCode', $locale);

        // set language in translator
        $translator = $this->getServiceLocator()->get('translator');
        $translator->setLocale($locale);

        // init static translator
        $tr = \Ffb\Common\I18n\Translator\Translator::Instance();
        $tr->setTranslator($translator);

    }

    /**
     * Import all
     *
     * This action will be called by a cronjob which can be called with:
     *     export APPLICATION_ENV=development
     *     php index.php tmsconfigtermination
     *
     * @return ConsoleModel
     */
    public function importallAction() {

        $consoleModel = new ConsoleModel();

        $result = '';
        try {

            $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

            // clear tables
            $qb = $em->getConnection()->createQueryBuilder();
            $qb->delete('product')->execute();
            $qb->delete('category')->execute();

//            $qb->delete('attribute_group')->execute();
//            $qb->delete('attribute')->execute();
//            $qb->delete('template')->execute();
//            $qb->delete('product')->execute();

//            $this->_importAttributes();
//            $this->_importAttributeGroups();
//            $this->_importCategories();

            $this->_importProducts();

        } catch (\Exception $e) {

            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            $result = $e->getMessage();
        }

        $consoleModel->setResult($result . PHP_EOL);

        return $consoleModel;

    }

    /**
     * Import products
     *
     * @throws \Exception
     * @throws \PHPExcel_Reader_Exception
     */
    private function _importProducts() {

        echo "import products" . PHP_EOL;

        // check if file exists
        $filename = '../dold/data/import/products.xlsx';
        if (!file_exists($filename)) {
            throw new \Exception($filename . ' doesn\'t exist');
        }

        // import config
        $chunkSize = 50;
        $maxRow    = 15000;

        // params
        $userModel = new Model\UserModel($this->getServiceLocator());
        $user = $userModel->findSysadmin();
        $productModel = new Model\ProductModel($this->getServiceLocator());
        $categoryModel = new Model\CategoryModel($this->getServiceLocator());
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        echo 'userid: ' . $user->getId() . PHP_EOL;

        // load to excel
        $filetype = \PHPExcel_IOFactory::identify($filename);
        $reader   = \PHPExcel_IOFactory::createReader($filetype);
        $reader->setReadDataOnly(true);

        $filter = new \PHPExcel_Reader_DefaultReadFilter();
        $reader->setReadFilter($filter);

        /* @var $excel \PHPExcel */
        $excel = $reader->load($filename);
        /* @var $sheet \PHPExcel_Worksheet */
        $sheet = $excel->getActiveSheet();

        $highestColumn = $sheet->getHighestColumn();
        $highestRow    = $sheet->getHighestRow();

        $rows = $sheet->rangeToArray('A1' . ':' . $highestColumn  . $highestRow, false, false, false);

        $category = null;
        foreach ($rows as $row) {

            // fields
            $isCategory      = strlen($row[0]) > 0;
            $nameDe          = $row[1];
            $nameTr          = $row[2];
            $amount          = $row[3];
            $price           = $row[4];

            if ($isCategory) {

                // add category
                $categoryData = array(
                    'translations' => array(
                        'de' => array(
                            'name' => $nameDe
                        ),
                        'tr' => array(
                            'name' => $nameTr
                        )
                    )
                );

                // build new category from data
                $category = $categoryModel->build($categoryData);
                $categoryModel->insert($category);

            } else {

                // add product
                $productData = array(
                    'translations' => array(
                        'de' => array(
                            'name' => $nameDe
                        ),
                        'tr' => array(
                            'name' => $nameTr
                        ),
                    ),
                    'categories' => array($category),
                    'price' => $price,
                    'amount' => $amount,
                );

                // insert
                $product = $productModel->build($productData);
                $productModel->insert($product);
            }

        }

        echo 'success' . PHP_EOL . PHP_EOL;
    }

    /**
     * $result = array(
     *      'articleGroup' => 'categoryId'
     * )
     *
     * @return array
     * @throws \Exception
     * @throws \PHPExcel_Reader_Exception
     */
    private function _getCategoryArticleGroups() {

        // model(s)
        $categoryModel = new Model\CategoryModel($this->getServiceLocator());
        $langModel = new Model\LangModel($this->getServiceLocator());

        $result = array();

        $filename = '../dold/data/import/categories.xlsx';

        // load to excel
        $filetype = \PHPExcel_IOFactory::identify($filename);
        $reader   = \PHPExcel_IOFactory::createReader($filetype);
        $reader->setReadDataOnly(true);

        $filter = new \PHPExcel_Reader_DefaultReadFilter();
        $reader->setReadFilter($filter);

        /* @var $excel \PHPExcel */
        $excel = $reader->load($filename);
        /* @var $worksheet \PHPExcel_Worksheet */
        $worksheet = $excel->getActiveSheet();

        $highestColumn = $worksheet->getHighestColumn();
        $highestRow    = $worksheet->getHighestRow();

        //// headers
        //$headers = $worksheet->rangeToArray('A1:' . $highestColumn . '1', '', false, false);
        //$headers = $headers[0];

        // 1 => de
        $lang = $langModel->findById(1);

        // get categories ids
        $allCategoryNames = $worksheet->rangeToArray('A2:A' . $highestRow, false, false, false);
        $allCategoryNames = array_column($allCategoryNames, 0);
        $categories = $categoryModel->findForExcelImport(array(
            'categoryNames' => $allCategoryNames,
            'langId' => $lang->getId()
        ));
        $indexedCategories = array();
        foreach ($categories as $categoryData) {
            $indexedCategories[$categoryData['categoryName']] = $categoryData['id'];
        }

        // category article groups
        $rows = $worksheet->rangeToArray('A2:' . $highestColumn . $highestRow, false, false, false);
        foreach($rows as $row) {

            $categoryName = $row[0];
            $articleGroup = $row[3];
            $parentCategoryName = $row[4];

            if (strlen($articleGroup) == 0) {
                continue;
            }

            // article groups
            $articleGroups = explode(';', $articleGroup);
            foreach ($articleGroups as $artGroup) {
                $artGroup = trim($artGroup);
                $result[$artGroup][] = $indexedCategories[$categoryName];
            }
        }

        return $result;
    }

    /**
     * Import categories
     *
     * @throws \Exception
     * @throws \PHPExcel_Reader_Exception
     */
    private function _importCategories() {

        echo "import categories" . PHP_EOL;

        // check if file exists
        $filename = '../dold/data/import/categories.xlsx';
        if (!file_exists($filename)) {
            throw new \Exception($filename . ' doesn\'t exist');
        }

        // model(s)
        $userModel = new Model\UserModel($this->getServiceLocator());
        $user = $userModel->findSysadmin();
        $categoryModel = new Model\CategoryModel($this->getServiceLocator());
        $templateModel = new Model\TemplateModel($this->getServiceLocator());
        $attributeGroupModel = new Model\AttributeGroupModel($this->getServiceLocator());
        $langModel = new Model\LangModel($this->getServiceLocator());

        // standard attributeGroup
        $attributeGroups = $attributeGroupModel->findByCriteria(array(
            'name' => 'Allgemeine Marketing-Daten'
        ));
        if (count($attributeGroups) > 0) {
            $attributeGroup = reset($attributeGroups);
        } else {
            throw new \Exception('Attribute group "Allgemeine Marketing-Daten" couldn\'t be found');
        }

        echo 'userid: ' . $user->getId() . PHP_EOL;

        // load to excel
        $filetype = \PHPExcel_IOFactory::identify($filename);
        $reader   = \PHPExcel_IOFactory::createReader($filetype);
        $reader->setReadDataOnly(true);

        $filter = new \PHPExcel_Reader_DefaultReadFilter();
        $reader->setReadFilter($filter);

        /* @var $excel \PHPExcel */
        $excel = $reader->load($filename);
        /* @var $worksheet \PHPExcel_Worksheet */
        $worksheet = $excel->getActiveSheet();

        $highestColumn = $worksheet->getHighestColumn();
        $highestRow    = $worksheet->getHighestRow();

        //// headers
        //$headers = $worksheet->rangeToArray('A1:' . $highestColumn . '1', '', false, false);
        //$headers = $headers[0];

        // get dataa
        $rows = $worksheet->rangeToArray('A2:' . $highestColumn . $highestRow, false, false, false);

        // 1 => de
        $lang = $langModel->findById(1);

        $categories = array();

        // insert categories
        foreach($rows as $row) {

            $categoryNameDe     = $row[0];
            $categoryNameEn     = $row[1];
            $categoryNameFr     = $row[2];
            $productGroup       = $row[3];
            $parentCategoryName = $row[4];

            $categoryData = array(
                'translations' => array(
                    'de' => array(
                        'name' => $categoryNameDe
                    ),
                    'en' => array(
                        'name' => $categoryNameEn
                    ),
                    'fr' => array(
                        'name' => $categoryNameFr
                    )
                )
            );

            // build new category from data
            $category = $categoryModel->build($categoryData);

            // create template for root category
            if (strlen($parentCategoryName) == 0) {

                $template = new Entity\TemplateEntity();
                $template->setName('Template: ' . $categoryNameDe);
                $category->setTemplate($template);

                $templateAttributeGroup = new Entity\TemplateAttributeGroupEntity();
                $templateAttributeGroup->setTemplate($template);
                $templateAttributeGroup->setAttributeGroup($attributeGroup);
                $template->addTemplateAttributeGroups(new Collections\ArrayCollection(array($templateAttributeGroup)));

                // insert template with attributeGroup Assignment
                $templateModel->insert($template);
            }

            // insert
            $categoryModel->insert($category);

            $categories[$categoryNameDe] = $category;

        }

        // insert assignments
        foreach($rows as $row) {

            $categoryNameDe     = $row[0];
            $categoryNameEn     = $row[1];
            $categoryNameFr     = $row[2];
            $productGroup       = $row[3];
            $parentCategoryName = $row[4];

            if (strlen($parentCategoryName) == 0) {
                continue;
            }

            $category = $categories[$categoryNameDe];
            $parentCategory = $categories[$parentCategoryName];

            // update
            $category->setParent($parentCategory);
            $categoryModel->update($category);

        }

        echo 'success' . PHP_EOL . PHP_EOL;
    }

    /**
     * Import attributeGroups
     *
     * @throws \Exception
     * @throws \PHPExcel_Reader_Exception
     */
    private function _importAttributeGroups() {

        echo "import attribute groups" . PHP_EOL;

        // check if file exists
        $filename = '../dold/data/import/attribute_groups.xlsx';
        if (!file_exists($filename)) {
            throw new \Exception($filename . ' doesn\'t exist');
        }

        // model(s)
        $userModel = new Model\UserModel($this->getServiceLocator());
        $user = $userModel->findSysadmin();
        $attributeModel = new Model\AttributeModel($this->getServiceLocator());
        $attributeGroupModel = new Model\AttributeGroupModel($this->getServiceLocator());

        echo 'userid: ' . $user->getId() . PHP_EOL;

        // load to excel
        $filetype = \PHPExcel_IOFactory::identify($filename);
        $reader   = \PHPExcel_IOFactory::createReader($filetype);
        $reader->setReadDataOnly(true);

        $filter = new \PHPExcel_Reader_DefaultReadFilter();
        $reader->setReadFilter($filter);

        /* @var $excel \PHPExcel */
        $excel = $reader->load($filename);
        /* @var $worksheet \PHPExcel_Worksheet */
        $worksheet = $excel->getActiveSheet();

        $highestColumn = $worksheet->getHighestColumn();
        $afterHighestColumn = $worksheet->getHighestColumn();
        $afterHighestColumn++;
        $highestRow    = $worksheet->getHighestRow();

        //// headers
        //$headers = $worksheet->rangeToArray('A1:' . $highestColumn . '1', '', false, false);
        //$headers = $headers[0];

        // insert attributeGroups
        for ($column = 'A'; $column != $afterHighestColumn; $column++) {

            $attributeGroupName = $worksheet->getCell($column . '1');
            $data = array(
                'translations' => array(
                    'de' => array(
                        'name' => $attributeGroupName
                    )
                )
            );

            $attributeGroup = $attributeGroupModel->build($data);

            // attribute assignments
            $attributesGroupAttributes = new Collections\ArrayCollection();
            for ($row = 2; $row <= $highestRow; $row++) {

                $cell = $worksheet->getCell($column . $row);
                if (strlen($cell) == 0) { continue; }
                $attribute = $attributeModel->findOneByName(array(
                    'name' => $cell,
                    'langCode' => 'de'
                ));

                if ($attribute) {
                    $aga = new Entity\AttributeGroupAttributeEntity();
                    $aga->setAttribute($attribute);
                    $attributesGroupAttributes->add($aga);
                }
            }
            $attributeGroup->addAttributeGroupAttributes($attributesGroupAttributes);

            // insert
            $attributeGroupModel->insert($attributeGroup);
        }

        echo 'success' . PHP_EOL . PHP_EOL;
    }

    /**
     * Import attributes
     *
     * @throws \Exception
     * @throws \PHPExcel_Reader_Exception
     */
    private function _importAttributes() {

        echo "import attributes" . PHP_EOL;

        // get user
        $attributeModel = new Model\AttributeModel($this->getServiceLocator());
        $userModel      = new Model\UserModel($this->getServiceLocator());
        $filename = '../dold/data/import/attributes.xlsx';
        $user = $userModel->findSysadmin();
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        // import config
        $chunkSize = 50;
        $maxRow    = 15000;

        // check if file exists
        if (!file_exists($filename)) {
            throw new \Exception($filename . ' doesn\'t exist');
        }

        echo 'userid: ' . $user->getId() . PHP_EOL;

        // load to excel
        $filetype = \PHPExcel_IOFactory::identify($filename);
        $reader   = \PHPExcel_IOFactory::createReader($filetype);
        $reader->setReadDataOnly(true);

        $filter = new \PHPExcel_Reader_DefaultReadFilter();
        $reader->setReadFilter($filter);

        /* @var $excel \PHPExcel */
        $excel = $reader->load($filename);
        /* @var $sheet \PHPExcel_Worksheet */
        $sheet = $excel->getActiveSheet();

        $highestColumn = $sheet->getHighestColumn();
        $highestRow    = $sheet->getHighestRow();

        //// headers
        //$headers = $sheet->rangeToArray('A1:' . $highestColumn . '1', '', false, false);
        //$headers = $headers[0];

        for ($row = 2; $row <= $maxRow; $row += $chunkSize) {

            // get next chunk
            $nextPart = ($row + $chunkSize - 1);
            if ($highestRow <= $nextPart) {
                $nextPart = $highestRow;
            }
            $rowsData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn  . $nextPart, false, false, false);

            if (count($rowsData) > 0) {

                // itearate chunk
                foreach ($rowsData as $key => $rowData) {

                    // fields
                    $nameDe         = $rowData[0];
                    $nameEn         = $rowData[1];
                    $nameFr         = $rowData[2];
                    $type           = $rowData[3];
                    $unit           = $rowData[4];
                    $optionValues   = $rowData[5];
                    $isMultiSelect  = $rowData[6];

                    // convert type
                    switch (strtolower($type)) {
                        case 'select'       : $type = Entity\AttributeEntity::TYPE_SELECT;      break;
                        case 'boolean'      : $type = Entity\AttributeEntity::TYPE_BOOL;        break;
                        case 'string'       : $type = Entity\AttributeEntity::TYPE_VARCHAR;     break;
                        case 'integer'      : $type = Entity\AttributeEntity::TYPE_INT;         break;
                        case 'float'        : $type = Entity\AttributeEntity::TYPE_FLOAT;       break;
                        case 'range-integer': $type = Entity\AttributeEntity::TYPE_RANGE_INT;   break;
                        case 'range-float'  : $type = Entity\AttributeEntity::TYPE_RANGE_FLOAT; break;
                        case 'text'         : $type = Entity\AttributeEntity::TYPE_TEXT;        break;
                        case 'image'        : $type = Entity\AttributeEntity::TYPE_IMAGE;       break;
                        case 'document'     : $type = Entity\AttributeEntity::TYPE_DOCUMENT;    break;
                        default:
                            $type = Entity\AttributeEntity::TYPE_VARCHAR;
                            break;
                    }

                    // convert isMultiSelect
                    switch (strtolower($isMultiSelect)) {
                        case 'ja'   :
                            $isMultiSelect = true;
                            break;
                        case 'nein' :
                        default:
                            $isMultiSelect = false;
                            break;
                    }

                    if ($type != Entity\AttributeEntity::TYPE_SELECT) {
                        $optionValues = '';
                        $isMultiSelect = false;
                    }

                    // new attributeData
                    $attributeData = array(
                        'translations' => array(
                            'de' => $nameDe,
                            'en' => $nameEn,
                            'fr' => $nameFr,
                            'es' => '',
                            'po' => '',
                            'ru' => '',
                        ),
                        'type' => $type,
                        'unit' => $unit,
                        'optionValues' => $optionValues,
                        'isMultiSelect' => $isMultiSelect,
                    );

                    $attribute = $attributeModel->build($attributeData);
                    $em->persist($attribute);

                }

                $em->flush();
                $em->clear();

                // processing status
                $processingStatus = (int)((($nextPart) / $highestRow) * 100);
                echo 'Process: ' . $processingStatus . '% imported' . PHP_EOL;
            }

            if ($highestRow <= $row) {
                break 1;
            }
        }

        echo 'success' . PHP_EOL . PHP_EOL;
    }

}
