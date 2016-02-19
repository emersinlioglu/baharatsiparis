<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Ffb\Common\File\Transfer\Adapter;

use Zend\File\Transfer;
use Zend\File\Transfer\Exception;

/**
 * File transfer adapter class for the HTTP protocol
 *
 */
class Ftp extends Transfer\Adapter\AbstractAdapter
{        
    /**
     * Connection Id
     *
     * @var integer 
     */
    private $_connectionId;

    /**
     *
     * @var string
     */
    private $_server;

    /**
     *
     * @var string
     */
    private $_ftpUser;

    /**
     *
     * @var string
     */
    private $_ftpPassword;

    /**
     *
     * @var boolean
     */
    private $_isPassive = false;

    /**
     * Constructor for Http File Transfers
     *
     * @param  array $options OPTIONAL Options to set
     * @throws Exception\PhpEnvironmentException if file uploads are not allowed
     */
    public function __construct($options)
    {

        $this->setOptions($options);
    }

    /**
     * Sets Options for adapters
     *
     * @param array $options Options to set
     * @param array $files   (Optional) Files to set the options for
     * @return AbstractAdapter
     */
    public function setOptions($options = array(), $files = null)
    {        

        if (   is_array($options)
            && isset($options['ftpConnection'])
        ) {
            $this->_server      = $options['ftpConnection']['server'];
            $this->_ftpUser     = $options['ftpConnection']['ftpUser'];
            $this->_ftpPassword = $options['ftpConnection']['ftpPassword'];
            $this->_isPassive   = $options['ftpConnection']['isPassive'];
        }

        return parent::setOptions($options, $files);
    }

    /**
     * Close connection on deconstuct
     *
     */
    public function __destruct() {

        if ($this->_connectionId) {
            ftp_close($this->_connectionId);
        }
    }

    /**
     * Send the file to the server (Upload)
     *
     * @param  string|array $options Options for the file(s) to send
     * @return void
     * @throws Exception\BadMethodCallException Not implemented
     */
    public function send($options = null)
    {
        throw new Exception\BadMethodCallException('Method not implemented');
    }    

    /**
     * Receive the file from the server (Download)
     *
     * @param  string|array $files (Optional) Files to receive
     * @return bool
     */
    public function receive($files = null)
    {
        if (!$files) {
            return false;
        }

        if (!$this->isConnected()) {
            throw new \Ffb\Common\File\Transfer\Exception\FtpConnectionFaultException('No ftp connection');
        }        

        if (is_string($files)) {
            $files = array($files);
        }

        foreach ($files as $file) {

            // get file to
            $fileTo = $this->getTmpDir() . '/' . $file;

            // Set the transfer mode
            $asciiArray = array('txt', 'csv');
            $filepart   = explode('.', $file);
            $extension  = end($filepart);
            if (in_array($extension, $asciiArray)) {
                $mode = FTP_ASCII;
            } else {
                $mode = FTP_BINARY;
            }

            // try to download $remote_file and save it to $handle
            if (ftp_get($this->_connectionId, $fileTo, $file, $mode, 0)) {
                
                $filedata = explode('/', $file);

                // guess mime
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $fileTo);
                finfo_close($finfo);

                /**
                 * Internal list of files
                 * This array looks like this:
                 *     array(form => array( - Form is the name within the form or, if not set the filename
                 *         name,            - Original name of this file
                 *         type,            - Mime type of this file
                 *         size,            - Filesize in bytes
                 *         tmp_name,        - Internally temporary filename for uploaded files
                 *         error,           - Error which has occurred
                 *         destination,     - New destination for this file
                 *         validators,      - Set validator names for this file
                 *         files            - Set file names for this file
                 *     ))
                 *
                 * @var array
                 */
                $this->files[] = array(
                    'name'        => $filedata,
                    'type'        => $mime,
                    'size'        => filesize($fileTo),
                    'tmp_name'    => $fileTo,
                    'error'       => null,
                    'destination' => null,
                    'validators'  => null,
                    'files'       => null
                );               
            } else {

                throw new Exception\BadMethodCallException('There was an error downloading file "' . $file . '" to "' . $fileTo . '"');
            }
        }

        return true;
    }

    /**
     * Checks if the file was already sent
     *
     * @param  string|array $files Files to check
     * @return bool
     * @throws Exception\BadMethodCallException Not implemented
     */
    public function isSent($files = null)
    {
        throw new Exception\BadMethodCallException('Method not implemented');
    }

    /**
     * Checks if the file was already received
     *
     * @param  string|array $files (Optional) Files to check
     * @return bool
     */
    public function isReceived($files = null)
    {
        throw new Exception\BadMethodCallException('Method not implemented');

//        $files = $this->getFiles($files, false, true);
//        if (empty($files)) {
//            return false;
//        }
//
//        foreach ($files as $content) {
//            if ($content['received'] !== true) {
//                return false;
//            }
//        }
//
//        return true;
    }

    /**
     * Checks if the file was already filtered
     *
     * @param  string|array $files (Optional) Files to check
     * @return bool
     */
    public function isFiltered($files = null)
    {

        throw new Exception\BadMethodCallException('Method not implemented');

//        $files = $this->getFiles($files, false, true);
//        if (empty($files)) {
//            return false;
//        }
//
//        foreach ($files as $content) {
//            if ($content['filtered'] !== true) {
//                return false;
//            }
//        }
//
//        return true;
    }

    /**
     * Has a file been uploaded ?
     *
     * @param  array|string|null $files
     * @return bool
     */
    public function isUploaded($files = null)
    {

        throw new Exception\BadMethodCallException('Method not implemented');

//        $files = $this->getFiles($files, false, true);
//        if (empty($files)) {
//            return false;
//        }
//
//        foreach ($files as $file) {
//            if (empty($file['name'])) {
//                return false;
//            }
//        }
//
//        return true;
    }

    /**
     * Check is connected
     *
     * @return boolean
     */
    public function isConnected() {

        return $this->_connectionId !== null;
    }

    /**
     * Create directory on server
     *
     * @param string $directory
     * @return boolean
     * @throws \Exception
     */
    public function makeDir($directory) {

        if (!$this->isConnected()) {
            throw new \Ffb\Common\File\Transfer\Exception\FtpConnectionFaultException('No ftp connection');
        }

        // If creating a directory is successful...
        if (!ftp_mkdir($this->_connectionId, $directory)) {
            throw new \Exception('Failed creating directory "' . $directory . '"');
        }

        return true;
    }

    /**
     * Set tmp dir for files
     *
     * @param string $tmpDir
     */
    public function setTmpDir($tmpDir) {

        $this->tmpDir = $tmpDir;
    }

    /**
     * Connect to ftp server
     *
     * @return boolean
     * @throws \Ffb\Common\File\Transfer\Exception\FtpConnectionFaultException
     */
    public function connect() {        

        // Set up basic connection
        $this->_connectionId = ftp_connect($this->_server);

        if (!$this->_connectionId) {
            $this->_connectionId = null;
            throw new \Ffb\Common\File\Transfer\Exception\FtpConnectionFaultException('FTP connection has failed');
        }

        // Login with username and password
        $loginResult = ftp_login($this->_connectionId, $this->_ftpUser, $this->_ftpPassword);

        // Sets passive mode on/off (default off)
        ftp_pasv($this->_connectionId, $this->_isPassive);

        // Check connection
        if (!$loginResult) {
            $this->_connectionId = null;
            throw new \Ffb\Common\File\Transfer\Exception\FtpConnectionFaultException('FTP login has failed');
        }

        return true;
    }

    /**
     * Check if file exist
     *
     * @param string $filename
     * @return boolean
     * @throws \Ffb\Common\File\Transfer\Exception\FtpConnectionFaultException
     */
    public function fileExists($filename) {

        if (!$filename) {
            return false;
        }

        if (!$this->isConnected()) {
            throw new \Ffb\Common\File\Transfer\Exception\FtpConnectionFaultException('No ftp connection');
        }

        // try to download $remote_file and save it to $handle
        return ftp_size($this->_connectionId, $filename) !== -1 ? true : false;
    }
}
