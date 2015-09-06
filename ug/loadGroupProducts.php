<?php

set_time_limit(0);
defined('MAGENTO_ROOT') || define('MAGENTO_ROOT', realpath(dirname(__FILE__) . '/../'));
require_once 'config.php';




$singleProducts = getGroupProducts($db);
$DefaultAttributeSetId = Mage::getSingleton('eav/config')
        ->getEntityType(Mage_Catalog_Model_Product::ENTITY)
        ->getDefaultAttributeSetId();
try {
    foreach ($singleProducts as $product) {
        $configProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $product['codart']);
        $category = Mage::getResourceModel('catalog/category_collection')->addFieldToFilter('name', ucwords(strtolower($product['tipoart'])));
        $cat = $category->getData();
        $categoryid = $cat[0]['entity_id'];
        $sku_product = trim($product['codgrupo'] . $product['codlinea'] . $product['codmodelo'] . $product['codmaterial'] . $product['codtemporada']);
        if (!$configProduct) {
            $configProduct = new Mage_Catalog_Model_Product();
            $configProduct->setAttributeSetId($DefaultAttributeSetId);
            $configProduct->setSku(trim($product['codart']));
            $configProduct->setTypeId('configurable');
            $configProduct->setName($product['descripcion']);
            $configProduct->setDescription($product['desclarga']);
            $configProduct->setShortDescription($product['desccorta']);
            $configProduct->setMetaTitle($product['marca'] . ' ' . $product['categoria']); //Meta title
            $configProduct->setMetaDescription($product['marca'] . ' ' . $product['categoria']); //Meta description
            $configProduct->setWebsiteIDs(array(1));
            //$configProduct->setWeight(1);
            $configProduct->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
            $configProduct->setTaxClassId(5);
            //$configProduct->setStockData(array('is_in_stock' => 1, 'manage_stock' => 1, 'use_config_manage_stock' => 1, 'qty' => $product['stock']));
            $configProduct->setCreatedAt(time());


            $configProduct->setData('grupo', getMagentoAttributeOptionId($db, 'grupo', $product['codgrupo']));
            $configProduct->setData('linea', getMagentoAttributeOptionId($db, 'linea', $product['codlinea']));
            $configProduct->setData('material', getMagentoAttributeOptionId($db, 'material', $product['codmaterial']));
            $configProduct->setData('subcategoria', getMagentoAttributeOptionId($db, 'subcategoria', $product['codsubcategoria']));
            $configProduct->setData('temporada', getMagentoAttributeOptionId($db, 'temporada', $product['codtemporada']));
            $configProduct->setData('modelo', getMagentoAttributeOptionId($db, 'modelo', $product['codmodelo']));
            $configProduct->setData('manufacturer', getMagentoAttributeOptionId($db, 'manufacturer', $product['marca']));



            //size id

            if ($categoryid) {
                $configProduct->setCategoryIds($categoryid);
            }

            $configProduct->setData('sku_parent', $sku_product);
            $configProduct->setStatus(1);

            $configProduct->setNewsFromDate(date('Y-m-d')); //product set as new from
            $configProduct->setNewsToDate(date("Y-m-d", strtotime("+2 week"))); //product set as new to
            ///buscamos los ids de los hijos
            $simpleProducts = Mage::getModel('catalog/product')->getCollection();

            $simpleProducts->addAttributeToSelect('sku_parent');
            $simpleProducts->addAttributeToSelect('talla');
            $simpleProducts->addAttributeToSelect('price');
            $simpleProducts->addFieldToFilter('sku_parent', trim($product['codart']));





            $configurableAttributeSizeValues = array();
            foreach ($simpleProducts as $simpleProduct) {
                $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($simpleProduct->getId());
                if (!in_array($configProduct->getId(), $parentIds)) {
                    $configurableAttributeSizeValues[] = array(
                        'label' => $simpleProduct->getAttributeText('talla'),
                        'value_index' => $simpleProduct->getData('talla'),
                        'is_percent' => false,
                        'pricing_value' => $simpleProduct->getPrice(),
                    );
                    $configProduct->setPrice($simpleProduct->getPrice());
                }
            }

            $configurableAttributeSize = array(
                'id' => null,
                'label' => 'Talla',
                'frontend_label' => 'Talla',
                'attribute_id' => 139,
                'attribute_code' => 'talla',
                'values' => $configurableAttributeSizeValues,
                'position' => 0,
            );


            $configurableAttributesData = array($configurableAttributeSize);
            $configurableProductsIds = array();
            foreach ($simpleProducts as $simpleProduct) {
                $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($simpleProduct->getId());
                if (!in_array($configProduct->getId(), $parentIds)) {
                    $configurableProductsIds[$simpleProduct->getId()] = $simpleProduct->getId();
                }
            }
            
            $configProduct->setStockData(array('is_in_stock' => 1, 'manage_stock' => 0, 'use_config_manage_stock' => 1,'qty' =>0));
            $configProduct->setConfigurableProductsData($configurableProductsIds);
            $configProduct->setConfigurableAttributesData($configurableAttributesData);
            $configProduct->setCanSaveConfigurableAttributes(true);
            $configProduct->save();
            
        } else {
            $configProduct->setUpdatedAt(strtotime('now'));
            $configurableAttributeSizeValues = array();
            foreach ($simpleProducts as $simpleProduct) {
                $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($simpleProduct->getId());
                if (!in_array($configProduct->getId(), $parentIds)) {
                    $configurableAttributeSizeValues[] = array(
                        'label' => $simpleProduct->getAttributeText('talla'),
                        'value_index' => $simpleProduct->getData('talla'),
                        'is_percent' => false,
                        'pricing_value' => 0,
                    );
                    $configProduct->setPrice($simpleProduct->getPrice());
                }
            }

            $configurableAttributeSize = array(
                'id' => null,
                'label' => 'Talla',
                'frontend_label' => 'Talla',
                'attribute_id' => 139,
                'attribute_code' => 'talla',
                'values' => $configurableAttributeSizeValues,
                'position' => 0,
            );


            $configurableAttributesData = array($configurableAttributeSize);
            $configurableProductsIds = array();
            foreach ($simpleProducts as $simpleProduct) {
                $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($simpleProduct->getId());
                if (!in_array($configProduct->getId(), $parentIds)) {
                    $configurableProductsIds[$simpleProduct->getId()] = $simpleProduct->getId();
                }
            }
            $configProduct->setConfigurableProductsData($configurableProductsIds);
            $configProduct->setConfigurableAttributesData($configurableAttributesData);
            $configProduct->setCanSaveConfigurableAttributes(true);
            $configProduct->getResource()->save($configProduct);
        }
    }
} catch (Exception $ex) {
    echo $ex->getMessage();
}