<?php

namespace Ffb\Backend\Service;

use Ffb\Backend\Model;
use Ffb\Backend\Entity;
use Ffb\Backend\Form;

use Zend\Session;

/**
 * Upload service class, provides some helpfull upload related functions
 *
 * @author murat.purc
 */
class UploadService extends \Ffb\Common\Service\AbstractUploadService {

    /**
     * Prepare file url for frontend.
     *
     * Uploaded files are now located in a folder that might not be accessible
     * for the webserver. In order to allow these files to be linked (e.g.
     * images) a controller action has to read this file from FS and return it.
     * The UploadController::publicAction() implments just this. So links to
     * images have to be rewritten so that this action is called.
     *
     * @param string $url
     * @return string
     */
    public function parseFrontendUrl($url) {

        $app = $this->_sl->get('Application\Config');
        $publicUploadFolder = $app['uploads']['public'];

        $resultUrl = str_replace($publicUploadFolder, '', $url);

        $resultUrl = parent::parseFrontendUrl($resultUrl);

        return $resultUrl;
    }

    /**
     * Udate uload for entity
     *
     * @param int $referenceId
     * @param string $referenceType
     * @param Entity\UserEntity $identity
     * @param string $token
     */
    public function udateUploads($referenceId, $referenceType, $identity, $token = null) {

        $uploadModel = new \Ffb\Backend\Model\UploadModel($this->_sl, $identity);
        $conf        = $this->_sl->get('Application\Config');

        // uploader
        $uploads = array();
        if ($token) {

            // read all uploads from session that are mapped
            // to this entity form by a token
            $uploads = $this->getUploadsFromSessionByToken($token);
        }

        /* @var $imageMngr \Ffb\Common\Image\ImageManager */
        $imageMngr = $this->_sl->get('Ffb\Common\Image\ImageManager');

        // go through uploads
        foreach ($uploads as $upload) {

            // upload entities in session are serialized .. so unserialize them
            /* @var $upload \Ffb\Backend\Entity\UploadEntity */
            $upload = unserialize($upload);

            if ((int)$upload->getId() > 0) {
                // upload exist in db, update rank, description or remove

                if ($upload->getToDelete() === true) {

                    // delete file
                    $path = $upload->getTmpName();
                    $uploadModel->delete($upload);
                    unlink($path);

                    // Delete thumbnails
                    $imageMngr->deleteThumbnails($path);
                } else {

                    // update file
                    $uploadModel->update($upload);
                }
            } else {

                // set parameters, upload
                $upload->setReferenceId($referenceId);
                $upload->setReferenceType($referenceType);

                // store upload and move file
                $succ = $this->moveUpload(
                    $upload,
                    $this->_getTempPath(),
                    $this->_getDesinationPath($referenceType, $referenceId)
                );
                $uploadModel->insert($succ);

                // Create thumbnails
                $imageMngr->createThumbnails($succ->getTmpName());
            }
        }
    }

    /**
     * Remove all uploads
     *
     * @param int $referenceId
     * @param string $referenceType
     */
    public function removeUploads($referenceId, $referenceType) {

        $conf = $this->_sl->get('Application\Config');

        // prepare destination path
        $remPath = array(
            $conf['uploads']['uploads'],
            $conf['uploads'][$referenceType],
            $referenceId
        );

        $uploadModel = new \Ffb\Backend\Model\UploadModel($this->_sl);
        $uploadModel->deleteEntities($uploadModel->findBy(array(
            'referenceType' => $referenceType,
            'referenceId'   => $referenceId
        )));

        $this->removeDir(implode('/', $remPath));
    }

    public function _getUploadForm() {

        $form = new \Ffb\Backend\Form\UploadForm();
        $form->setAttribute('action',
            $this->_url->fromRoute('home/default', array(
                'controller' => 'upload',
                'action'     => 'upload'
            ))
        );

        // form uses upload filter requiring path to temp upload folder
//        $app = $this->_sl->get('Application\Config');
//        $form->setTempUploadFolder($app['uploads']['temp']);

        return $form;
    }

    /**
     * Return current upload model
     *
     * @return \Ffb\Backend\Model\UploadModel
     */
    protected function _getUploadModel() {
        return new Model\UploadModel($this->getServiceLocator(), $this->_logger, $this->getUser());
    }

    /**
     * Return current upload entity
     *
     * @return \Ffb\Backend\Entity\UploadEntity
     */
    protected function _getUploadEntity() {
        return new Entity\UploadEntity();
    }

    /**
     * Return current destination path
     *
     * @param string $referenceType
     * @param integer $referenceId
     * @return string
     */
    public function _getDestinationPath($referenceType, $referenceId) {

        $config = $this->_sl->get('Application\Config');

        // prepare destination path
        $destPath = array(
            $config['uploads'][$referenceType],
            $referenceId
        );

        return implode('/', $destPath);
    }

    /**
     * Return current temp path
     *
     * @return string
     */
    public function _getTempPath() {

        $config = $this->_sl->get('Application\Config');
        return $config['uploads']['temp'];
    }
}