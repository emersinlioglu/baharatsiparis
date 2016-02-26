<?php

namespace Ffb\Backend\Controller;

use Ffb\Backend\Model;
use Ffb\Backend\Entity;
use Ffb\Backend\Form;
use Ffb\Backend\Service;
use Ffb\Backend\View\Helper;

use Zend\Json\Json;
use Zend\Mvc;
use Zend\View\Model as ZendModel;
use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 * @author erdal.mersinlioglu
 */
class TestController extends AbstractBackendController {

    /**
     * @param Mvc\MvcEvent $e
     * @return mixed
     */
    public function onDispatch(Mvc\MvcEvent $e) {
        $this->layout('layout/empty');
        return parent::onDispatch($e);
    }

    /**
     * Skip authentication
     */
    public function checkAuthentication() {
        return true;
    }

    /**
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction() {

        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        // param(s)
        $param = $this->params()->fromRoute('param', null);
        $value = $this->params()->fromRoute('value', null);
        $value2 = $this->params()->fromRoute('value2', null);

        $data = array(
            'key' => 's3Cu1ZL5To',
            'language' => 'de'
        );

        $action = 'categories';

        $restClient = $this->_getRestApiClientService();
        $response = $restClient->post(
            $action,
            $data
        );

        $view->setVariables(array(
            'content' => Json::encode($response)
        ));

        return $view;
    }

    /**
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function categoriesAction() {

        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        // param(s)
        $language   = $this->params()->fromRoute('value', 'de');
        $categoryId = $this->params()->fromRoute('value2', null);

        $data = array(
            'key' => 's3Cu1ZL5To',
            'language' => $language,
            'categoryId' => $categoryId
        );

        $action = 'categories';

        $restClient = $this->_getRestApiClientService();
        $response = $restClient->post(
            $action,
            $data
        );

        $view->setVariables(array(
            'content' => Json::encode($response)
        ));

        return $view;
    }

    /**
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function categoryproductsAction() {

        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        // param(s)
        $language   = $this->params()->fromRoute('value', 'de');
        $categoryId = $this->params()->fromRoute('value2', null);

        $action = 'categoryproducts';
        $data = array(
            'key' => 's3Cu1ZL5To',
            'language' => $language,
            'categoryId' => $categoryId
        );

        $restClient = $this->_getRestApiClientService();
        $response = $restClient->post(
            $action,
            $data
        );

        $view->setVariables(array(
            'content' => Json::encode($response)
        ));

        return $view;
    }

    /**
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function productdetailsAction() {

        $view = new ZendModel\ViewModel(array(
            'state' => 'ok'
        ));

        // param(s)
        $language   = $this->params()->fromRoute('value', 'de');
        $productId  = $this->params()->fromRoute('value2', null);

        $action = 'productdetails';
        $data = array(
            'key' => 's3Cu1ZL5To',
            'language' => $language,
            'productId' => $productId
        );

        $restClient = $this->_getRestApiClientService();
        $response = $restClient->post(
            $action,
            $data
        );

        $view->setVariables(array(
            'content' => Json::encode($response)
        ));

        return $view;
    }

    /**
     * RestApi client service
     * @return Service\RestApiClientService
     */
    protected function _getRestApiClientService() {

        $uri = $this->getRequest()->getUri();
        $restClient = new Service\RestApiClientService(
        //URL of shopware REST server
            $uri->getScheme() . '://' . $uri->getHost() . '/api',
            '4fb',
            'demo'
        );

        return $restClient;
    }

}
