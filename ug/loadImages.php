<?php

set_time_limit(0);
defined('MAGENTO_ROOT') || define('MAGENTO_ROOT', realpath(dirname(__FILE__) . '/../'));
require_once 'config.php';




$images = getImages($db);
$DefaultAttributeSetId = Mage::getSingleton('eav/config')
        ->getEntityType(Mage_Catalog_Model_Product::ENTITY)
        ->getDefaultAttributeSetId();
$media = Mage::getModel('catalog/product_attribute_media_api');
try {
    $importDir = Mage::getBaseDir('media') . DS . 'catalog/import/';
    foreach ($images as $image) {
        $data = explode('_', $image['img_name']);
        $sku = $data[0];
        $order = explode('.', $data[1]);
        $position = intval($order[0]);
        $productMagento = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

        if ($productMagento) {
            $filePath = $importDir . strtolower($image['img_name']);
            if (file_exists($filePath)) {
                try {

                    $imageObj = new Varien_Image($filePath);
                    $imageObj->constrainOnly(TRUE);
                    $imageObj->keepAspectRatio(TRUE);
                    $imageObj->keepFrame(FALSE);
                    $imageObj->resize(640, 480);
                    $imageObj->save($filePath);

                    $to_add = true;
                    $pathInfo = pathinfo($fileName);


                    $types = ($position == 1) ? array('image', 'small_image', 'thumbnail') : array();

                    $items = $media->items($productMagento->getId());
                    foreach ($items as $item) {

                        if (strpos($item['file'], strtolower($image['img_name'])) > 0) {
                            $to_add = false;
                        }
                    }


                    if ($to_add) {
                        $productMagento->addImageToMediaGallery($filePath, $types, false, false);
                        $productMagento->save();
                    }
                } catch (Exception $e) {
                    echo $filePath.' '.$e->getMessage() . '<br>';
                }
               // unlink($filePath);
            }
        }
    }

    // die($sku);
} catch (Exception $ex) {
    echo $ex->getMessage();
}