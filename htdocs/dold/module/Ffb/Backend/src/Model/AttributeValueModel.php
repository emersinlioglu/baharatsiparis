<?php

namespace Ffb\Backend\Model;

use Ffb\Backend\Entity;

use Zend\ServiceManager;

/**
 * @author erdal.mersinlioglu
 */
class AttributeValueModel extends AbstractBaseModel {

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param \Zend\Log\Logger $logger
     * @param Entity\UserEntity $identity
     */
    public function __construct(
            ServiceManager\ServiceLocatorInterface $sl,
            \Zend\Log\Logger $logger = null,
            Entity\UserEntity $identity = null) {
        $this->_entityClass = 'Ffb\Backend\Entity\AttributeValueEntity';
        parent::__construct($sl, $logger, $identity);
    }

    /**
     * Get files array for upload input
     *
     * @param int    $attributeValueId
     * @param string $destination
     * @return array
     */
    public function getFilesForUploadInput($attributeValueId, $destination = null) {

        $uploadModel = new UploadModel($this->_sl);
        $result      = array();
        $files       = $uploadModel->findByReference(
                Entity\AttributeValueEntity::FILE_REF, $attributeValueId, $destination);

        if (count($files) === 0) {
            return $result;
        }

        /* @var $imageMngr \Ffb\Common\Image\ImageManager */
        $imageMngr = $this->_sl->get('Ffb\Common\Image\ImageManager');

        /* @var $uploadService \Ffb\Tms\Service\UploadService */
        $uploadService = $this->_sl->get('Ffb\Backend\Service\UploadService');

        /* @var $file \Ffb\Tms\Entity\UploadEntity */
        foreach ($files as $file) {

            // get image resolution
            $resolution = $uploadService->getImageSize($file->getTmpName());

            // images
            $imageView    = $imageMngr->getThumbnail($file->getTmpName(), 'uploadTablePreview', true);
            $imageGallery = $imageMngr->getThumbnail($file->getTmpName(), 'uploadGalleryPreview', true);

            // save file values
            $result[] = array(
                'id'   => $file->getId(),
                'name' => $file->getName(),
                'rank' => $file->getRank(),
                'size' => $uploadService->sizeToString($file->getSize()),
                'description' => $file->getDescription(),
                'resolution' => $resolution['width'] . 'x' . $resolution['height'],
                'url' => $uploadService->parseFrontendUrl($file->getTmpName()),
                'urlView' => $uploadService->parseFrontendUrl($imageView),
                'urlGallery' => $uploadService->parseFrontendUrl($imageGallery)
            );
        }

        return $result;
    }

    /**
     * Determines the attributeValue of a product with given filters
     *
     * @param \Ffb\Backend\Entity\ProductEntity $parent
     * @param int $attributeGroupId
     * @param int $attributeLangId
     * @param int $langId
     * @return Entity\AttributeValueEntity
     */
    public function findParentAttributeValue(Entity\ProductEntity $parent = null, $attributeGroupId, $attributeLangId, $langId) {

        // get parent attribute value
        $pAttributeValue = null;
        if ($parent) {
            foreach ($parent->getTranslations() as $pProductLang) {
                if ($pProductLang->getLang()->getId() == $langId) {

                    foreach ($pProductLang->getAttributeValues() as $pAttributeValue) {
                        if ($pAttributeValue->getAttributeGroup()->getId() == $attributeGroupId
                            && $pAttributeValue->getAttributeLang()->getId() == $attributeLangId
                        ) {
                            $pAttributeValue = $pAttributeValue;
                            break;
                        }
                    }
                    break;
                }
            }
        }

        return $pAttributeValue;
    }

    /**
     * Parses value options in array
     *
     * @param $value
     * @return array
     */
    public static function parseOptionValues($value) {
        return str_getcsv($value, ';', '"');
    }

    /**
     * Encodes array values in csv format as string
     *
     * @param array $values
     * @return string
     */
    public static function encodeOptionValues(array $values) {

        $result = array();
        foreach ($values as $key => $value) {
            $result[] = '"' . $value . '"';
        }

        return $value = implode(';', $result);
    }


}