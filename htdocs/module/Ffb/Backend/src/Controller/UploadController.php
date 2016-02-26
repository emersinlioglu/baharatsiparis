<?php

namespace Ffb\Backend\Controller;

use Ffb\Common\Stdlib\ArrayUtils;
use Ffb\Backend\Entity;

use Zend\Json\Json;
use Zend\View\Model;

/**
 * This controller has two actions, "index" displays the upload form "upload"
 * performs the upload.
 *
 * Upload of files can either be performed for an existing entity or for an
 * entity that is currently created. An existing entity is identified by ID.
 *
 * An entity that is currently created has a token that will be used to
 * store the upload data into session until the entity is created and
 * written to database.
 *
 * @author erdal.mersinliolu
 */
class UploadController extends AbstractBackendController {

    /**
     * Area name for the rights
     * @var string
     */
    const AREA = 'upload';

    /**
     * No dispatch actions
     *
     */
    public function preDispatch() {

    }

    /**
     * Creates and returns the AttendeeService.
     * Some controller specific objects are provided to the service.
     *
     * @param Entity\UserEntity $me
     * @return \Ffb\Tms\Service\AttendeeService
     */
    private function _getUploadService(Entity\UserEntity $me) {

        $service = $this->getServiceLocator()->get('Ffb\Backend\Service\UploadService');
        $service->setFlashMessenger($this->flashMessenger());
        $service->setTranslator($this->translator);
        $service->setUrl($this->url());
        $service->setLogger($this->logger);
        $service->setUser($me);

        return $service;
    }

    /**
     * Display upload form.
     * Is called via Ajax. Has to be called by POST!
     *
     * @return Model\ViewModel
     */
    public function indexAction() {

        // this action should always be requested via POST
        if (!$this->getRequest()->isPost()) {
            throw new \Exception('method not supported');
        }

        $view = new Model\ViewModel(array(
            'state' => 'ok'
        ));

        // try to delete data
        try {

            // gather data from POST request (and FILES!)
            $data = $this->getRequest()->getPost()->toArray();

            // perform file upload via service
            /* @var $service \Ffb\Tms\Service\UploadService */
            $service = $this->_getUploadService($this->auth->getIdentity());
            $service->fileform($view, $data);

        } catch (\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            $this->flashMessenger()->addMessage($e->getMessage());
            $view->setVariable('state', 'error');
        }

        return $view;
    }

    /**
     * Perform upload.
     * Is called via Ajax. Has to be called by POST!
     *
     * @return Model\ViewModel
     */
    public function uploadAction() {

        // this action should always be requested via POST
        if (!$this->getRequest()->isPost()) {
            throw new \Exception('method not supported');
        }

        $view = new Model\ViewModel(array(
            'state' => 'ok'
        ));

        try {

            // gather data from POST request (and FILES!)
            $postData = $this->getRequest()->getPost()->toArray();
            $filesData = $this->getRequest()->getFiles()->toArray();

            $data = ArrayUtils::merge($postData, $filesData);

            // show form if no files were uploaded
            if (empty($filesData)) {
                $this->flashMessenger()->addMessage($this->translator->translate('MSG_NO_FILES'));
                $view->setVariable('state', 'error');
                return $view;
            }

            // perform file upload via service
            /* @var $service \Ffb\Backend\Service\UploadService */
            $service = $this->_getUploadService($this->auth->getIdentity());
            $service->fileupload($view, $data);

            // prepare view for ajax or iframe upload (not ajax for old browsers)
            if (!$this->getRequest()->isXmlHttpRequest()) {

                $data = $view->getVariables();

                // Set flash messages to json
                $messages = $this->flashMessenger()->getCurrentMessages();
                if (count($messages) > 0) {
                    $data['messages'] = $messages;
                }

                // Create JsonModel and update output template to ajax.tpl
                $jsonView = new JsonModel(array(
                    'json' => Json::encode($data)
                ));
                $jsonView->setTemplate('ajax/index');

                return $jsonView;
            }

        } catch (\Exception $e) {

            $this->_handleException($e, $view);
        }

        return $view;
    }

    /**
     * Perform delete.
     * Is called via Ajax. Has to be called by POST!
     *
     * @return Model\ViewModel
     */
    public function deleteAction() {

        // this action should always be requested via POST
        if (!$this->getRequest()->isPost()) {
            throw new \Exception('method not supported');
        }

        $view = new Model\ViewModel(array(
            'state' => 'ok'
        ));

        // try to delete data
        try {

            // gather data from POST request (and FILES!)
            $data = $this->getRequest()->getPost()->toArray();

            // perform file upload via service
            /* @var $service \Ffb\Backend\Service\UploadService */
            $service = $this->_getUploadService($this->auth->getIdentity());
            $service->filedelete($view, $data);

        } catch (\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            $this->flashMessenger()->addMessage($e->getMessage());
            $view->setVariable('state', 'error');
        }

        return $view;
    }

    /**
     * Updade rank and decription for file
     *
     * @return Model\ViewModel
     */
    public function updateAction() {

        // this action should always be requested via POST
        if (!$this->getRequest()->isPost()) {
            throw new \Exception('method not supported');
        }

        $view = new Model\ViewModel(array(
            'state' => 'ok'
        ));

        // try to delete data
        try {

            // gather data from POST request (and FILES!)
            $data = $this->getRequest()->getPost()->toArray();

            // perform file upload via service
            /* @var $service \Ffb\Backend\Service\UploadService */
            $service = $this->_getUploadService($this->auth->getIdentity());
            $service->fileupdate($view, $data);

        } catch (\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            $this->flashMessenger()->addMessage($e->getMessage());
            $view->setVariable('state', 'error');
        }

        return $view;
    }
}