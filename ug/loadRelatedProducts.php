<?php

set_time_limit(0);
defined('MAGENTO_ROOT') || define('MAGENTO_ROOT', realpath(dirname(__FILE__) . '/../'));
require_once 'config.php';




$singleProducts = getRelatedProducts($db);
$DefaultAttributeSetId = Mage::getSingleton('eav/config')
        ->getEntityType(Mage_Catalog_Model_Product::ENTITY)
        ->getDefaultAttributeSetId();
try {
    foreach ($singleProducts as $product) {
        $configProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $product['codart']);
        $category = Mage::getResourceModel('catalog/category_collection')->addFieldToFilter('name', ucwords(strtolower($product['tipoart'])));

        if ($configProduct) {

            $sku_product = trim($product['codgrupo'] . $product['codlinea'] . $product['codmodelo'] . $product['codmaterial'] . $product['codtemporada']);


            $linkData = array();

            Mage::getResourceModel('catalog/product_link')->saveProductLinks(
                    $configProduct, $linkData, Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED
            );

            $simpleProducts = Mage::getModel('catalog/product')->getCollection();
            $simpleProducts->addAttributeToSelect('sku_parent');
            $simpleProducts->addFieldToFilter('sku_parent', $sku_product);

            $position = 1;
            
          
            foreach ($simpleProducts as $simpleProduct) {
                $id = $simpleProduct->getId();
                if (intval($id) != intval($configProduct->getId())) {
                    $linkData[$id] = array('position' => $position);
                    $position++;
                }
            }
            //echo count($simpleProducts).'<br>';
            Mage::getResourceModel('catalog/product_link')->saveProductLinks(
                    $configProduct, $linkData, Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED
            );
            $configProduct->save();
        }
      
    }
} catch (Exception $ex) {
    echo $ex->getMessage();
}