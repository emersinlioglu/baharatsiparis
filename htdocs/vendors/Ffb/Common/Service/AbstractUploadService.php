<?php

namespace Ffb\Common\Service;

use Zend\Session;

/**
 * Upload service class, provides some helpfull upload related functions
 *
 * @author murat.purc, ilja.schwarz
 */
abstract class AbstractUploadService extends AbstractService {

    /**
     * Session uploads key
     *
     * @var string
     */
    const SESSION_KEY_UPLOADS = 'uploads';

    /**
     *
     * @var string
     */
    const THUMBNAIL_PREVIEW_TABLE = 'uploadTablePreview';

    /**
     *
     * @var string
     */
    const THUMBNAIL_PREVIEW_GALLERY = 'uploadGalleryPreview';

    /**
     * Prepare file url for frontend.
     *
     * @param string $url
     * @return string
     */
    public function parseFrontendUrl($url) {

        return str_replace('\\', '/', $url);
    }

    /**
     * Get image size
     *
     * @param string $imagePath
     * @return array
     *    - $arr['width']  (int|null)
     *    - $arr['height']  (int|null)
     */
    public function getImageSize($imagePath = null) {

        $result = array(
            'height' => null,
            'width'  => null
        );

        if (!$imagePath) {
            return $result;
        }

        if (file_exists($imagePath)) {
            $size = getimagesize($imagePath);
            $result['width'] = $size[0];
            $result['height'] = $size[1];
        }

        return $result;
    }

    /**
     * Move uploaded file
     *
     * @param \Ffb\Common\Entity\AbstractBaseEntity $upload
     * @param string $source
     * @param string $destination
     * @throws Exception
     *         if file could not be found
     * @return \Ffb\Common\Entity\AbstractBaseEntity
     */
    public function moveUpload(\Ffb\Common\Entity\AbstractBaseEntity $upload, $source, $destination) {

        // file is moved from temp to location
        $currentPath = $upload->getTmpName();
        $dest = str_replace($source, $destination, $currentPath);

        // encode filename for the uploaded file
        // @see DERTRA-805
        // $filename = filename($dest);
        // $filename = substr($filename, 0 , (strrpos($filename, ".")));
        $filename = pathinfo($dest, PATHINFO_FILENAME);

        // rename filename
        // strtolower destroys input encoding, rendering a filename possibly broken
        $newfilename = mb_strtolower($filename);

        $map = array('ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss');
        $newfilename = str_replace(array_keys($map), array_values($map), $newfilename);
        $newfilename = preg_replace('/[^a-z0-9]/u', '_', $newfilename);

        $dest = str_replace($filename, $newfilename, $dest);

        // amend path in entity
        $upload->setTmpName($dest);

        // move file from temp to uploads
        if (file_exists($currentPath)) {
            $destDir = dirname($dest);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0777, true);
            }
            // DERTMS-846: move, not copy
            rename($currentPath, $dest);
            chmod($dest, 0775);
        } else {
            throw new \Exception('file could not be found');
        }

        // return updated upload
        return $upload;
    }

    /**
     * Copy file
     *
     * @param string $source
     * @param string $destination
     * @return UploadEntity
     */
    public function copyFolder($source, $destination) {

        if (!file_exists($destination)) {
            mkdir($destination, 0775, true);
            chmod($destination, 0775);
        }

        foreach ($iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(
                        $source,
                        \RecursiveDirectoryIterator::SKIP_DOTS
                    ),
                    \RecursiveIteratorIterator::SELF_FIRST
                ) as $item
        ) {
            if ($item->isDir()) {

                mkdir($destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName());

            } else {

                if (file_exists($item)) {
                    copy($item, $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                }
            }
        }

    }

    /**
     * Copies all files of a reference type & id under the new reference id
     *
     * @param string $refType
     * @param int $refId
     * @param int $newRefId
     */
    public function copyFiles($refType, $refId, $newRefId) {

        // config
        $appConf = $this->_sl->get('Application\Config');

        // get model(s)
        $uploadModel = $this->_getUploadModel();

        // determine uploads
        $uploads = $uploadModel->findBy(array(
            'referenceType' => $refType,
            'referenceId' => $refId
        ));

        if (count($uploads) > 0) {

            // uploads
            foreach ($uploads as $upload) {

                // clone upload, TODO update path to get function
                $upload = clone $upload;
                $upload->setReferenceId($newRefId);
                $newTmpname = basename($upload->getTmpname());
                $newTmpname = $appConf['uploads']['path'][$refType] . '/' . $newRefId . '/' . $newTmpname;
                $upload->setTmpname($newTmpname);

                // insert upload
                $uploadModel->insert($upload);
            }

            // copy folder, TODO update path to get function
            $src = $appConf['uploads']['path'][$refType] . '/' . $refId;
            $dst = $appConf['uploads']['path'][$refType] . '/' . $newRefId;
            $this->copyFolder($src, $dst);
        }
    }

    /**
     * Deletes the upload directory for a specific reference type & id
     *
     * @param string $refType
     * @param int $refId
     */
    public function deleteDirectoryFor($refType, $refId) {

        // config, TODO update path to get function
        $appConf = $this->_sl->get('Application\Config');
        $src = $appConf['uploads']['path'][$refType] . '/' . $refId;

        $this->deleteDirectory($src);
    }

    /**
     * Deletes a directory recursively
     *
     * @param string $target Path
     */
    public function deleteDirectory($target) {

        if (is_dir($target)){
            $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned

            foreach($files as $file) {
                $this->deleteDirectory($file);
            }

            if (file_exists($target)) {
                rmdir($target);
            }
        } else if (is_file($target)) {

            unlink($target);
        }
    }

    /**
     * Convert file size to string
     *
     * @param int $size
     * @return string
     */
    public function sizeToString($size = null) {

        if (!$size) {
            return '';
        }

        $filesize = $size;
        if ($filesize > 1000000) {
            $filesize = (int) ($filesize / 1000000) . ' MB';
        } else if ($filesize > 1000) {
            $filesize = (int) ($filesize / 1000) . ' KB';
        } else {
            $filesize .= ' B';
        }

        return $filesize;
    }

    /**
     * Remove dir recursively
     *
     * @param string $dir
     * @return type
     */
    public function removeDir($dir) {

        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->removeDir("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }

    /**
     * Read file chunked
     *
     * @param string $filename
     * @return boolean
     */
    public function readfileChunked($filename) {

        $chunksize = 1*(1024*1024); // how many bytes per chunk
        $buffer    = '';
        $handle    = fopen($filename, 'rb');

        if ($handle === false) {
            return false;
        }

        while (!feof($handle)) {
            $buffer = fread($handle, $chunksize);
            print $buffer;
            ob_flush();
            flush();
        }

        return fclose($handle);
    }

    /**
     * Reads uploads from given session and returns them as array of upload
     * entities. Uploads are moved to the correct folder.
     *
     * $session = new Session\Container('base');
     * // check that key exists in session
     * $session->offsetExists('email');
     * // getting value from the session by key
     * $email = $session->offsetGet('email');
     * // setting value in session
     * $session->offsetSet('email', $email);
     *
     * @param string $token
     * @param string $key
     * @return mixed
     */
    public function getUploadsFromSessionByToken($token, $key = self::SESSION_KEY_UPLOADS) {

        // create new session by token
        $session = new Session\Container($key);

        // check for existance of uploads in session
        if (!$session->offsetExists($token)) {
            return array();
        }

        // build array of upload entities from session
        $uploads = $session->offsetGet($token);

        return $uploads;
    }

    /**
     * Remove all uploads for given token
     *
     * @param string $token
     * @param string $key
     */
    public function removeUploadsFromSessionByToken($token, $key = self::SESSION_KEY_UPLOADS) {

        // create new session by token
        $session = new Session\Container($key);

        // check for existance of uploads in session
        if (!$session->offsetExists($token)) {
            return;
        }

        // remove token in session
        $session->offsetUnset($token);
    }

    /**
     * @todo move mime tpye check to a form validator
     * @param \Zend\View\Model\ViewModel $view
     * @param array $data
     * @param boolean $emulateFileUpload
     * @throws \Exception
     * @return \Zend\View\Model\ViewModel
     */
    public function fileupload(\Zend\View\Model\ViewModel $view, array $data, $emulateFileUpload = false) {

        // try to persist data
        try {

            // get values
            $uploadType    = $data['uploadType'];
            $token         = isset($data['token']) ? $data['token'] : null;
            $referenceType = isset($data['referenceType']) ? $data['referenceType'] : '';
            $referenceId   = isset($data['referenceId']) ? $data['referenceId'] : '';
            $destination   = isset($data['destination']) ? $data['destination'] : null;

            // assert that either token or referenceType and referenceId are given
            if (   0 === strlen($token)
                && (0 === strlen($referenceId) || 0 === strlen($referenceType))
            ) {
                throw new \Exception('upload can be performed for an existing entity or for a token .. both are missing');
            }

            $config = $this->_sl->get('Application\Config');

            // don't allow some special chars to avoid XSS injection
            $blacklistedChars = array('\\', '/', ':', '?', '<', '>', '|');
            foreach ($blacklistedChars as $blacklistedChar) {
                if (false !== mb_strpos($data['upload']['name'], $blacklistedChar)) {
                    throw new \Exception('MSG_INVALID_FILE_NAME');
                }
            }

            // don't allow files that are bigger than maxFileSize pro destination, or 5MB
            $maxFilesize = isset($config['uploads']['max_file_size']['default']) ? $config['uploads']['max_file_size']['default'] : 5245329;

            if (   $destination
                && isset($config['uploads']['max_file_size'][$destination])
            ) {
                $maxFilesize = $config['uploads']['max_file_size'][$destination];
            }
            if ($maxFilesize < filesize($data['upload']['tmp_name'])) {
                throw new Exception\MaxFileSizeException('MSG_INVALID_FILE_SIZE', (int)$maxFilesize);
            }

            // determine mime type groups
            if (isset(
                $destination,
                $config['uploads']['destination'],
                $config['uploads']['destination'][$destination]
            )) {
                // mime type groups are defined by destination
                $mimeTypeGroups = $config['uploads']['destination'][$destination];
            } else {
                // mime type groups are defined by upload type
                $mimeTypeGroups = array($uploadType);
            }

            // determine mime types
            $mimeTypes = array();
            foreach ($mimeTypeGroups as $mimeTypeGroup) {
                if (!isset($config['uploads']['mime'][$mimeTypeGroup])) {
                    continue;
                }
                $new = $config['uploads']['mime'][$mimeTypeGroup];
                $mimeTypes = array_merge($mimeTypes, $new);
            }

            // check MIME type of uploaded file
            $mimeType = $this->_getMimeType($data['upload']);
            if (!in_array($mimeType, $mimeTypes)) {
                error_log('mime type ' . print_r($mimeType,1) . ' is not one of '. print_r($mimeTypes,1) . ' for destination ' . print_r($destination,1));
                $message = 'images' === $uploadType ? 'MSG_INVALID_IMAGE_TYPE' : 'MSG_UPLOAD_INVALID_FILE_TYPE';
                //$message .= ': ' . $mimeType;
                throw new \Exception($message);
            }

            // check, gd or imagemagick cann work with a file as image
            if ('images' === $uploadType) {

                // Do simple validation the code below uses getimagesize()
                // and it returns false in case of an error or non image
                $resolution = $this->getImageSize($data['upload']['tmp_name']);
                if (!$resolution['width'] || !$resolution['height']) {
                    throw new \Exception('MSG_INVALID_IMAGE');
                }
            }

            // build form w/ input filter
            /* @var $form Ffb\Common\Form\AbstractUploadForm */
            $form = $this->_getUploadForm();
            if ($emulateFileUpload) {

                /* @var $inpF InputFilter\InputFilter */
                $inputFilter = $form->getInputFilter();
                $inputFilter->remove('upload');

                $filters    = $form->getFilters();
                $validators = $form->getValidators();

                $uploadFilter = $filters['move_upload'];
                if ($form->getTempUploadFolder()) {
                    $uploadFilter['options']['target'] = $form->getTempUploadFolder();
                }
                $inputFilter->add(array(
                    'name'       => 'upload',
                    'filters'    => array($uploadFilter),
                    'validators' => array($validators['not_empty'])
                ));
                $form->setInputFilter($inputFilter);
            }
            $form->setData($data);
            $form->prepare();

            // check form validity (and move upload into temp folder!)
            if (!$form->isValid()) {
                $view->setVariable('invalidFields', $form->getInvalidFields());
                throw new \Exception('MSG_FORM_INVALID');
            }

            // get validated entity from form
            $data = $form->getData();

            // build entity from validated form data
            // due to the fact that the form data is a nested array
            // this cannot be automated :(
            $upload = $this->_getUploadEntity();
            $upload->setName($data['upload']['name']);
            $upload->setMimetype($data['upload']['type']);
            $upload->setTmpName($data['upload']['tmp_name']);
            $upload->setSize($data['upload']['size']);
            if (array_key_exists('description', $data)) {
                $upload->setDescription($data['description']);
            }
            $upload->setDestination($data['destination']);

            // check token or direct upload
            if (0 < strlen($token)) {

                // set rank
                $uploads = $this->getUploadsFromSessionByToken($token);
                $upload->setRank(count($uploads) + 1);

                // append entity to session (using token)
                array_push($uploads, serialize($upload));
                $session = new Session\Container(self::SESSION_KEY_UPLOADS);
                $session->offsetSet($token, $uploads);

                list($imageView, $imageGallery) = $this->_processAndGetUploadImages(
                    $uploadType,
                    $referenceType,
                    $upload
                );

                // get image resolution
                $resolution = '';
                if ('images' === $uploadType) {
                    $resolutionData = $this->getImageSize($upload->getTmpName());
                    $resolution = $resolutionData['width'] . 'x' . $resolutionData['height'];
                }

                $view->setVariables(array(
                    'file' => array(
                        'id'          => null,
                        'name'        => $upload->getName(),
                        'url'         => $this->parseFrontendUrl($upload->getTmpName()),
                        'urlView'     => $this->parseFrontendUrl($imageView),
                        'urlGallery'  => $this->parseFrontendUrl($imageGallery),
                        'rank'        => $upload->getRank(),
                        'size'        => $this->sizeToString($upload->getSize()),
                        'description' => $upload->getDescription(),
                        'resolution'  => $resolution
                    )
                ));

            } else if (0 < strlen($referenceType) && 0 < strlen($referenceId)) {

                // get model(s)
                $uploadModel = $this->_getUploadModel();

                // store entity via model
                $upload->setReferenceType($referenceType);
                $upload->setReferenceId($referenceId);

                // set rank
                if (!$upload->getRank()) {
                    $upload->setRank(
                        $uploadModel->countByReference(
                            $upload->getReferenceId(),
                            $upload->getReferenceType(),
                            $upload->getDestination()
                        ) + 1
                    );
                }

                // get pathes
                $tempPath = $this->_getTempPath();
                $destPath = $this->_getDestinationPath($referenceType, $referenceId);

                // move file
                $upload = $this->moveUpload(
                    $upload,
                    $tempPath,
                    $destPath
                );

                // insert into db
                $uploadModel->insert($upload);

                list($imageView, $imageGallery) = $this->_processAndGetUploadImages(
                    $uploadType,
                    $referenceType,
                    $upload
                );

                // get image resolution
                $resolution = '';
                if ('images' === $uploadType) {
                    $resolutionData = $this->getImageSize($upload->getTmpName());
                    $resolution = $resolutionData['width'] . 'x' . $resolutionData['height'];
                }

                $view->setVariables(array(
                    'file' => array(
                        'id'          => $upload->getId(),
                        'name'        => $upload->getName(),
                        'url'         => $this->parseFrontendUrl($upload->getTmpName()),
                        'urlView'     => $this->parseFrontendUrl($imageView),
                        'urlGallery'  => $this->parseFrontendUrl($imageGallery),
                        'rank'        => $upload->getRank(),
                        'size'        => $this->sizeToString($upload->getSize()),
                        'description' => $upload->getDescription(),
                        'resolution'  => $resolution
                    )
                ));
            }

            $this->_flashMessenger->addMessage($this->_translator->translate('MSG_FILE_UPLOADED'));
        } catch (\Exception $e) {

            // DERTMS-846 remove temporary file on failure
            if (isset($data) && isset($data['upload'], $data['upload']['tmp_name'])) {
                $filename = $data['upload']['tmp_name'];
                if (is_file($filename)) {
                    unlink($filename) || error_log('could not unlink ' . $filename);
                }
            }

            throw $e;
        }
    }

    /**
     * DERTMS-966 Don't use the ['type'] parameter to validate uploads.
     * That field is user-provided, and can be trivially forged,
     * allowing ANY type of file to be uploaded.
     *
     * @link http://stackoverflow.com/a/11601457
     * @param array $upload
     */
    private function _getMimeType(array $upload) {

        if (!extension_loaded('fileinfo')) {
            throw new \Exception('PHP extension "fileinfo" is not installed which is crucial for secure file upload');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $upload['tmp_name']);
        finfo_close($finfo);

        return $mime;

    }

    /**
     * @param \Zend\View\Model\ViewModel $view
     * @param array $data
     * @throws \Exception
     * @return \Zend\View\Model\ViewModel
     */
    public function filedelete(\Zend\View\Model\ViewModel $view, array $data) {

        // get values
        $token       = array_key_exists('token', $data) ? $data['token'] : null;
//        $referenceId = array_key_exists('referenceId', $data) ? (int) $data['referenceId'] : null;
        $fileId      = array_key_exists('fileId', $data) ? (int) $data['fileId'] : null;
        $fileName    = array_key_exists('fileName', $data) ? $data['fileName'] : null;
        $destination = array_key_exists('destination', $data) ? $data['destination'] : null;

        // assert that either token or referenceType and referenceId are given
        if (!$token && !$fileId) {
            throw new \Exception('need a token, file id or reference id');
        }

        // check file data
        if (!$fileId && !$fileName) {
            $view->setVariables(array(
                'state' => 'error',
                'messages' => array($this->_translator->translate('MSG_NOT_ENOUGH_PARAMETERS'))
            ));
            return;
        }

        // get model(s)
        $uploadModel = $this->_getUploadModel();

        if ($token) {

            // get files from session
            $uploads = $this->getUploadsFromSessionByToken($token);
            $found   = false;

            foreach ($uploads as $key => $upload) {

                $upl = unserialize($upload);

                // if file has id, file in db, mark to deleting
                if (
                    0 < (int) $upl->getId()
                    && $upl->getId() === $fileId
                ) {
                    $upl->setToDelete(true);
                    $found = true;
                }

                // if file has no id, it is in session, search by name and destination
                if (
                    0 === (int) $upl->getId()
                    && $upl->getName() === $fileName
                    && (!$destination || $upl->getDestination() === $destination)
                ) {
                    $upl->setToDelete(true);
                    $found = true;
                }

                $uploads[$key] = serialize($upl);
            }

            //if file not found in session and has id, add to session with toDelet = true
            if (!$found && (int) $fileId > 0) {
                $upl = $uploadModel->findById($fileId);
                $upl->setToDelete(true);
                $uploads[] = serialize($upl);
            }

            // save changes to session
            $session = new Session\Container(self::SESSION_KEY_UPLOADS);
            $session->offsetSet($token, $uploads);

        } else if ($fileId) {

            // remove file from db
            $upload = $uploadModel->findById($fileId);
            if ($upload) {

                $uploadModel->deleteEntity($upload);

                /* @var $imageMngr \Ffb\Common\Image\ImageManager */
                $imageMngr = $this->_sl->get('Ffb\Common\Image\ImageManager');

                // get config if exist
                $config = $this->_sl->get('Config');
                if (isset($config['images'][$upload->getReferenceType()])) {
                    $imageMngr->setConfig($config['images'][$upload->getReferenceType()]);
                }

                // delete thumbnails
                $imageMngr->deleteThumbnails($upload->getTmpName());

                // delete file
                @unlink($upload->getTmpName());
            }
        }

        $view->setVariable('messages', array($this->_translator->translate('MSG_FILE_DELETED')));
    }

    /**
     * @param \Zend\View\Model\ViewModel $view
     * @param array $data
     * @throws \Exception
     * @return \Zend\View\Model\ViewModel
     */
    public function fileform(\Zend\View\Model\ViewModel $view, array $data) {

        // build form w/ input filter
        $form = $this->_getUploadForm();
        $form->setData($data);

        // get values
        $token         = $form->get('token')->getValue();
        $referenceType = $form->get('referenceType')->getValue();
        $referenceId   = $form->get('referenceId')->getValue();
        $uploadType    = $form->get('uploadType')->getValue();

        $form->setAttribute('class', 'form-default form-upload form-' . $uploadType);

        // assert that either token or referenceType and referenceId are given
        if (   0 == strlen($token)
            && (!$referenceId || 0 == strlen($referenceType))
        ) {
            throw new \Exception('upload can be performed for an existing '
                               . 'entity or for a token .. both are missing');
        }

        // build headline & info
        switch ($uploadType) {
            case 'participantsimport':
                $headline = 'TTL_UPLOAD_FORM_PARTICIPANTSIMPORT';
                $info     = 'MSG_PARTICIPANTSIMPORT_UPLOAD';
                $infoOld  = 'MSG_PARTICIPANTSIMPORT_UPLOAD_OLD_BROWSER';
                break;
            case 'contracts':
                $headline = 'TTL_UPLOAD_FORM_CONTRACT';
                $info     = 'MSG_IMAGES_UPLOAD_INFO';
                $infoOld  = 'MSG_IMAGES_UPLOAD_INFO_OLD_BROWSER';
                break;
            case 'documents':
                $headline = 'TTL_UPLOAD_FORM_DOCUMENT';
                $info     = 'MSG_DOCUMENTS_UPLOAD_INFO';
                $infoOld  = 'MSG_DOCUMENTS_UPLOAD_INFO_OLD_BROWSER';
                break;
            case 'images':
                $headline = 'TTL_UPLOAD_FORM_IMAGE';
                $info     = 'MSG_IMAGES_UPLOAD_INFO';
                $infoOld  = 'MSG_IMAGES_UPLOAD_INFO_OLD_BROWSER';
                break;
            default:
                $headline = 'TTL_UPLOAD_FORM_UNKNOWN';
                $info     = 'MSG_UNKNOWN_UPLOAD_INFO';
                $infoOld  = 'MSG_UNKNOWN_UPLOAD_INFO_OLD_BROWSER';
        }

        // build & show view
        $view->setVariables(array(
            'form'     => $form->prepare(),
            'headline' => $headline,
            'info'     => $info,
            'infoOld'  => $infoOld,
            'state'    => 'ok'
        ));
    }

    /**
     * @param \Zend\View\Model\ViewModel $view
     * @param array $data
     * @throws \Exception
     * @return \Zend\View\Model\ViewModel
     */
    public function fileupdate(\Zend\View\Model\ViewModel $view, array $data) {

        try {

            $fieldName = array_key_exists('name', $data) ? $data['name'] : null;
            switch ($fieldName) {

                case 'description':
                    $this->_updateDescription($data);
                    break;

                case 'rank':
                    $this->_updateRank($data);
                    break;

                default:
                    throw new \Exception('wrong value name');
            }

        } catch (\Exception $e) {
            $view->setVariable('state', 'error');
            $this->_flashMessenger()->addMessage($e->getMessage());
        }
    }

    /**
     * Update file description
     *
     * @param array $data POST data
     * @throws \Exception
     * @return \Zend\View\Model\ViewModel
     */
    private function _updateDescription($data){

        // get values
        $token         = array_key_exists('token', $data) ? $data['token'] : null;
        $fileId        = array_key_exists('fileId', $data) ? (int) $data['fileId'] : null;
        $fileName      = array_key_exists('fileName', $data) ? $data['fileName'] : null;
//        $referenceId   = array_key_exists('referenceId', $data) ? $data['referenceId'] : null;
        $description   = array_key_exists('value', $data) ? $data['value']:'';
        $destination   = array_key_exists('destination', $data) ? $data['destination'] : null;

        // assert that either token or fileId are given
        if (!$token && !$fileId) {
            throw new \Exception('need a token or file id');
        }

        // get model(s)
        $uploadModel = $this->_getUploadModel();

        if ($token) {

            // get files from session#
            $uploads = $this->getUploadsFromSessionByToken($token);
            $found   = false;

            foreach ($uploads as $key => $upload) {

                $upl = unserialize($upload);

                if ((int) $upl->getId() > 0 && $upl->getId() === $fileId) {

                    // if file has id, file in db, mark to deleting
                    $upl->setDescription($description);
                    $found = true;

                } else if (
                    (int) $upl->getId() === 0     &&
                    $upl->getName() === $fileName &&
                    (!$destination || $upl->getDestination() === $destination)
                ) {

                    // if file has no id, it is in session, search by name and destination
                    $upl->setDescription($description);
                    $found = true;

                }

                $uploads[$key] = serialize($upl);
            }

            //if file not found in session and has id, add to session
            if (!$found && (int) $fileId > 0) {

                // get upload
                $upl = $uploadModel->findById($fileId);
                $upl->setDescription($description);
                $uploads[] = serialize($upl);
            }

            // save changes to session
            $session = new Session\Container(self::SESSION_KEY_UPLOADS);
            $session->offsetSet($token, $uploads);

        } else if ($fileId) {

            // update file in db
            $upload = $uploadModel->findById($fileId);
            $upload->setDescription($description);
            $uploadModel->update($upload);
        }
    }

    /**
     * Update file rank
     *
     * @param array $data POST data
     * @throws \Exception
     * @return \Zend\View\Model\ViewModel
     */
    private function _updateRank($data){

        // get values
        $token         = array_key_exists('token', $data) ? $data['token'] : null;
        $referenceId   = array_key_exists('referenceId', $data) ? (int) $data['referenceId'] : null;
        $referenceType = array_key_exists('referenceType', $data) ? $data['referenceType'] : null;
        $fileId        = array_key_exists('fileId', $data) ? (int) $data['fileId'] : null;
        $fileName      = array_key_exists('fileName', $data) ? $data['fileName'] : null;
        $destination   = array_key_exists('destination', $data) ? $data['destination'] : null;
        $rank          = array_key_exists('value', $data) ? $data['value'] : null;

        // assert that either token or referenceType and referenceId are given
        if (!$token && !$fileId) {
            throw new \Exception('need a token, file id or reference id');
        }

        // get model(s)
        $uploadModel = $this->_getUploadModel();

        if ($token) {

            // get files from session
            $uploads = $this->getUploadsFromSessionByToken($token);
            $found   = false;

            // get all uploads to update to rank 0
            $allUploads = $uploadModel->findByReference($referenceType, $referenceId, $destination);

            // fill uploads with files from db to set rank to 0
            foreach ($uploads as $key => $upload) {

                $upl = unserialize($upload);

                // check upl id in allUploads, if not exist add to uploads with rank 0
                if ((int) $upl->getId() > 0) {

                    $inAllUploads = false;
                    foreach ($allUploads as $dbUpload) {
                        if ($upl->getId() === $dbUpload->getId()) {
                            $inAllUploads = true;
                        }
                    }

                    // if in all Uploads but not in current array, add to update in controller
                    if (!$inAllUploads) {
                        $upl->setRank(0);
                        $uploads[] = serialize($upl);
                    }
                }
            }

            foreach ($uploads as $key => $upload) {

                $upl = unserialize($upload);

                if ((int) $upl->getId() > 0 && $upl->getId() === $fileId) {

                    // if file has id, file in db, mark to deleting
                    $upl->setRank($rank);
                    $found = true;

                } else if (
                    (int) $upl->getId() === 0     &&
                    $upl->getName() === $fileName &&
                    (!$destination || $upl->getDestination() === $destination)
                ) {

                    // if file has no id, it is in session, search by name and destination
                    $upl->setRank($rank);
                    $found = true;

                } else {

                    // if not found by id or name set to 0
                    $upl->setRank(0);

                }

                $uploads[$key] = serialize($upl);
            }

            //if file not found in session and has id, add to session
            if (!$found && (int) $fileId > 0) {

                // get upload
                $upl = $uploadModel->findById($fileId);
                $upl->setRank($rank);
                $uploads[] = serialize($upl);
            }

            // save changes to session
            $session = new Session\Container(self::SESSION_KEY_UPLOADS);
            $session->offsetSet($token, $uploads);

        } else if ($fileId) {

            // update file in db
            $upload = $uploadModel->findById($fileId);

            // update other ranks
            $uploadModel->setRankByCriteries(
                0,
                $upload->getReferenceType(),
                $upload->getReferenceId(),
                $upload->getDestination()
            );

            // update upload
            $upload->setRank($rank);
            $uploadModel->update($upload);
        }
    }

    /**
     * Create thumbnails if needed and return proper image urls for view and gallery
     * @param string $uploadType
     * @param string $referenceType  Note: $upload->getReferenceType() is not always available!
     * @param \Ffb\Common\Entity\AbstractBaseEntity $upload
     * @return array
     */
    private function _processAndGetUploadImages($uploadType, $referenceType, \Ffb\Common\Entity\AbstractBaseEntity $upload) {

        if ('images' === $uploadType) {
            // generate thumbnails

            $configModule = $this->_sl->get('Config');

            if (array_key_exists($referenceType, $configModule['images'])) {
                $imageConfig = $configModule['images'][$referenceType];
            } else {
                $imageConfig = $configModule['images']['default'];
            }

            /* @var $imageMngr \Ffb\Common\Image\ImageManager */
            $imageMngr = $this->getServiceLocator()->get('Ffb\Common\Image\ImageManager');
            $imageMngr->setConfig($imageConfig);

            // create thumnbails
            $imageMngr->createThumbnails($upload->getTmpName());

            // update original image
            $imageMngr->resize($upload->getTmpName());

            // get images for upload controls
            $imageView    = $imageMngr->getThumbnail($upload->getTmpName(), self::THUMBNAIL_PREVIEW_TABLE, true);
            $imageGallery = $imageMngr->getThumbnail($upload->getTmpName(), self::THUMBNAIL_PREVIEW_GALLERY, true);
        } else {

            $imageView    = $upload->getTmpName();
            $imageGallery = $upload->getTmpName();
        }

        return array($imageView, $imageGallery);
    }

    /**
     * Get secific upload form
     *
     * @return Ffb\Common\Form\AbstractUploadForm
     */
    abstract protected function _getUploadForm();

    /**
     * Get specific upload model
     *
     */
    abstract protected function _getUploadModel();

    /**
     * Get specific upload entity
     *
     */
    abstract protected function _getUploadEntity();

    /**
     * Get specific destination path
     *
     */
    abstract protected function _getDestinationPath($referenceType, $referenceId);

    /**
     * Get specific destination path
     *
     */
    abstract protected function _getTempPath();
}
