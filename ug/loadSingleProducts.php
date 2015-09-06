<?php

set_time_limit(0);
defined('MAGENTO_ROOT') || define('MAGENTO_ROOT', realpath(dirname(__FILE__) . '/../'));
require_once 'config.php';




$singleProducts = getSingleProducts($db);

$DefaultAttributeSetId = Mage::getSingleton('eav/config')
        ->getEntityType(Mage_Catalog_Model_Product::ENTITY)
        ->getDefaultAttributeSetId();
foreach ($singleProducts as $product) {
    $productMagento = Mage::getModel('catalog/product')->loadByAttribute('sku', $product['codart']);
    if (!$productMagento) {
        $productMagento = new Mage_Catalog_Model_Product();
        $productMagento->setAttributeSetId($DefaultAttributeSetId);
        $productMagento->setData('sku_parent', trim($product['codpadre']));
        $productMagento->setSku(trim($product['codart']));
        $productMagento->setTypeId('simple');
        $productMagento->setName($product['descripcion']);
        $productMagento->setDescription($product['desclarga']);
        $productMagento->setShortDescription($product['desccorta']);
        $productMagento->setMetaTitle($product['marca'] . ' ' . $product['categoria']); //Meta title
        $productMagento->setMetaDescription($product['marca'] . ' ' . $product['categoria']); //Meta description
        $productMagento->setWebsiteIDs(array(1));
        $productMagento->setWeight(1);
        $productMagento->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        $productMagento->setTaxClassId(5);
        $productMagento->setStockData(array('is_in_stock' => 1, 'manage_stock' => 1, 'use_config_manage_stock' => 1, 'qty' => $product['stock']));
        $productMagento->setCreatedAt(time());


        $productMagento->setData('color', getMagentoAttributeOptionId($db, 'color', $product['codcolor']));
        $productMagento->setData('grupo', getMagentoAttributeOptionId($db, 'grupo', $product['codgrupo']));
        $productMagento->setData('linea', getMagentoAttributeOptionId($db, 'linea', $product['codlinea']));
        $productMagento->setData('material', getMagentoAttributeOptionId($db, 'material', $product['codmaterial']));
        $productMagento->setData('talla', getMagentoAttributeOptionId($db, 'talla', $product['codtalla']));




        $productMagento->setData('subcategoria', getMagentoAttributeOptionId($db, 'subcategoria', $product['codsubcategoria']));
        $productMagento->setData('temporada', getMagentoAttributeOptionId($db, 'temporada', $product['codtemporada']));
        $productMagento->setData('modelo', getMagentoAttributeOptionId($db, 'modelo', $product['codmodelo']));
        $productMagento->setData('manufacturer', getMagentoAttributeOptionId($db, 'manufacturer', $product['marca']));
    }
    $productMagento->setUpdatedAt(strtotime('now'));
    $activo = (intval($product['activo']) === 1) ? 1 : 2;
    $productMagento->setStatus($activo);
    $productMagento->setCost($product['costomn']);
    $productMagento->setPrice($product['ventamn']);
    // seteamos el color
    $productMagento->save();

    sleep(1);
}