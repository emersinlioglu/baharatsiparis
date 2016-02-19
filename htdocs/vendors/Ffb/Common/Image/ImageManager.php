<?php

namespace Ffb\Common\Image;

use \Imagine\Gd\Imagine;
use \Imagine\Image\Box;

/**
 * ImageManager class
 * 
 * @author Murat Purc
 */
class ImageManager {

    /**
     * Template for thumbnail suffix
     * @var string
     */
    const THUMBNAIL_SUFFIX_TPL = '_%sx%s';

    /**
     * Imagine instance
     * @var Imagine
     */
    protected $_imagine = null;

    /**
     * Configuration
     * @var array
     */
    protected $_config = array();

    /**
     * Imagine setter
     * @param \Imagine\Gd\Imagine $imagine
     */
    public function setImagine(Imagine $imagine) {
        $this->_imagine = $imagine;
    }

    /**
     * Imagine getter
     * @return \Imagine\Gd\Imagine
     */
    public function getImagine() {
        return $this->_imagine;
    }

    /**
     * Configuration setter
     * @param array $config
     */
    public function setConfig(array $config) {
        $this->_config = $config;
    }

    /**
     * Configuration getter
     * @return array
     */
    public function getConfig() {
        return $this->_config;
    }

    /**
     * Creates thumbnails of an image given by path
     * @param string $path
     */
    public function createThumbnails($path) {

        $imagine = $this->getImagine();
        $image   = $imagine->open($path);

        foreach ($this->_config['thumbnails'] as $entry) {

            $thumbPath = $this->getThumbnail($path, $entry['name']);
            if (null === $thumbPath) {
                continue;
            }

            if (isset($entry['crop']) && (int)$entry['crop'] === 1) {
                $mode = \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
            } else {
                $mode = \Imagine\Image\ImageInterface::THUMBNAIL_INSET;
            }

            $thumbnail = $image->thumbnail(new Box($entry['width'], $entry['height']), $mode);
            $thumbnail->save($thumbPath);
        }
    }

    /**
     * Deletes thumbnails of an image given by path
     * @param string $path
     */
    public function deleteThumbnails($path) {

        foreach ($this->_config['thumbnails'] as $entry) {

            $thumbPath = $this->getThumbnail($path, $entry['name']);
            if (null === $thumbPath) {
                continue;
            }

            if (is_file($thumbPath)) {
                unlink($thumbPath);
            }
        }
    }

    /**
     * Returns a thumbnail of an image by thumbnail name
     * @param string $path
     * @param string $name
     * @param bool $useFallback
     * @return string|null
     */
    public function getThumbnail($path, $name, $useFallback = false) {
        $suffix = $this->buildThumbnailSuffix($name);

        if (null === $suffix) {
            return null;
        }

        $fileInfo      = new \SplFileInfo($path);
        $newPath       = $fileInfo->getPath();
        $ext           = $fileInfo->getExtension();
        $baseName      = $fileInfo->getBasename('.' + $ext);
        $imagePathName = "{$newPath}/{$baseName}{$suffix}.{$ext}";

        if (true === $useFallback) {
            if (!is_file($imagePathName)) {
                $imagePathName = $path;
            }
        }

        return $imagePathName;
    }

    /**
     * Returns list of all available thumbnails of an image
     * @param string $path
     * @return string[]
     */
    public function getThumbnails($path) {
        $thumbnails = array();

        foreach ($this->_config['thumbnails'] as $entry) {
            $thumbPath = $this->getThumbnail($path, $entry['name']);
            if (null === $thumbPath || !is_file($thumbPath)) {
                continue;
            }
            $thumbnails[] = $thumbPath;
        }

        return $thumbnails;
    }

    /**
     * Builds the thumbnail suffix
     * @param string $name
     * @return string|null
     */
    public function buildThumbnailSuffix($name) {
        $cfg = $this->getThumbnailCfgByName($name);

        if (null === $cfg) {
            return null;
        }

        return sprintf(self::THUMBNAIL_SUFFIX_TPL, $cfg['width'], $cfg['height']);
    }

    /**
     * Returns the thumbnail configuration by it's name
     * @param string $name
     * @return array|null
     */
    public function getThumbnailCfgByName($name) {

        foreach ($this->_config['thumbnails'] as $entry) {

            if ($entry['name'] === $name) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * Resize original image if config exist
     * 
     * @param string $path
     */
    public function resize($path) {

        if (!isset($this->_config['resize'])) return;

        $resizeConf = $this->_config['resize'];

        $imagine = $this->getImagine();
        $image   = $imagine->open($path);
        
        if (isset($resizeConf['crop']) && (int)$resizeConf['crop'] === 1) {
            $mode = \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
        } else {
            $mode = \Imagine\Image\ImageInterface::THUMBNAIL_INSET;
        }

        $thumbnail = $image->thumbnail(new Box($resizeConf['width'], $resizeConf['height']), $mode);
        $thumbnail->save($path);
    }
}