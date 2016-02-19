<?php

/**
 * Update RenameUpload to work with direct uploaded files
 */
namespace Ffb\Common\Filter\File;

use Zend\Filter\Exception;
use Zend\Stdlib\ErrorHandler;

/**
 */
class RenameUpload extends \Zend\Filter\File\RenameUpload {

    /**
     *
     * @param string $sourceFile
     *         Source file path
     * @param string $targetFile
     * @throws Exception\RuntimeException
     * @return bool
     */
    protected function moveUploadedFile($sourceFile, $targetFile) {
        ErrorHandler::start();
        $result = rename($sourceFile, $targetFile);
        $warningException = ErrorHandler::stop();
        if (! $result || null !== $warningException) {
            throw new Exception\RuntimeException ( sprintf ( "File '%s' could not be renamed. An error occurred while processing the file.", $sourceFile ), 0, $warningException );
        }

        return $result;
    }

}
