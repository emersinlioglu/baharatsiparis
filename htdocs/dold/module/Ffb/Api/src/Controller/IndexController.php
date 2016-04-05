<?php

namespace Ffb\Api\Controller;

use Ffb\Api\Model\CategoryModel;
use Ffb\Api\Model\ProductModel;
use Zend\Db\Adapter\Adapter;
use Zend\View\Model as ZendModel;

/**
 *
 * @author erdal.mersinlioglu
 */
class IndexController extends AbstractApiController {

    /**
     * categoriesAction
     *
     * @return ZendModel\ViewModel
     */
    public function categoriesAction() {

        $view = new ZendModel\JsonModel(array(
            'state' => 'ok',
            'action' => 'categories'
        ));

        try {

            $categoryModel = new CategoryModel($this->getServiceLocator());

            // post params
            $data = $this->getRequest()->getPost();
            $language   = $data->get('language', $this->getMasterLang());
            $categoryId = $data->get('categoryId');

            // get response data
            $responseData = $categoryModel->getCategoriesData($language, $categoryId);

            $view->setVariables($responseData);

        } catch (\Exception $ex) {

            $this->_handleException($view, $ex);
        }

        return $view;
    }

    /**
     * categoryproductsAction
     *
     * @return ZendModel\ViewModel
     */
    public function categoryproductsAction() {

        $view = new ZendModel\JsonModel(array(
            'state' => 'ok',
            'action' => 'categoryproducts'
        ));

        try {
            // get model(s)
            $productModel = new ProductModel($this->getServiceLocator());

            // post params
            $data = $this->getRequest()->getPost();
            $language   = $data->get('language', $this->getMasterLang());
            $categoryId = $data->get('categoryId');
            $count      = $data->get('count');
            $offset     = $data->get('offset', 0);

            //set hostname
            $uri = $this->getRequest()->getUri();
            $host = $uri->getScheme() . '://' . $uri->getHost();
            $productModel->setHost($host);

            // get response data
            $responseData = $productModel->getCategoryProductsData($language, $categoryId, $count, $offset);
            $view->setVariables($responseData);

        } catch (\Exception $ex) {

            $this->_handleException($view, $ex);
        }

        return $view;
    }

    /**
     * productdetailsAction
     *
     * @return ZendModel\ViewModel
     */
    public function productdetailsAction() {

        $view = new ZendModel\JsonModel(array(
            'state' => 'ok',
            'action' => 'productdetails'
        ));

        try {
            // get model(s)
            $productModel = new ProductModel($this->getServiceLocator());

            // post params
            $data = $this->getRequest()->getPost();
            $language   = $data->get('language', $this->getMasterLang());
            $productId  = $data->get('productId');

            // get response data
            $responseData = $productModel->getProductsDetailsData($language, $productId);
            $view->setVariables($responseData);

        } catch (\Exception $ex) {

            $this->_handleException($view, $ex);
        }

        return $view;
    }

}