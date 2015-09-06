<?php

error_reporting(E_ALL);
if (!defined('MAGENTO_ROOT')) {
    header('HTTP/1.1 404 Not Found');
    die();
}
defined('ZF_PATH') || define('ZF_PATH', MAGENTO_ROOT . '/lib');
set_include_path(get_include_path() . PATH_SEPARATOR . ZF_PATH);
$mageFilename = MAGENTO_ROOT . '/app/Mage.php';
require_once $mageFilename;
require_once 'Zend/Loader/Autoloader.php';
require_once 'Zend/Db.php';
Zend_Loader_Autoloader::getInstance();
Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));

$options = array(
    Zend_Db::CASE_FOLDING => Zend_Db::CASE_LOWER,
    Zend_Db::AUTO_QUOTE_IDENTIFIERS => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8"'
);

$adapter['username'] = 'bferr_lenovo';
$adapter['password'] = '0p9o8i7u6y5t4r3e2w1q!';
$adapter['dbname'] = 'bferr_bruno';
$adapter['host'] = '199.195.119.130';
$adapter['options'] = $options;
$db = new Zend_Db_Adapter_Pdo_Mysql($adapter);

function getSingleProducts($db) {
    $select = $db->select();
    $select->from(array('a' => 'vw_productos'));
    $select->where('a.codpadre <>""');

    return $db->fetchAll($select);
}

function getGroupProducts($db) {
    $select = $db->select();
    $select->from(array('a' => 'productos'));
    $select->where('a.codpadre =""');
    return $db->fetchAll($select);
}

function getRelatedProducts($db) {
    $select = $db->select();
    $select->from(array('a' => 'productos'));
    $select->where('a.codpadre =""');
    return $db->fetchAll($select);
}

function getImages($db) {
    $select = $db->select();
    $select->from(array('a' => 'images'));
    return $db->fetchAll($select);
}

function getMagentoAttributeOptionId($db, $attribute, $valor) {
    $atributo_codigo = trim($attribute);
    $label = trim($valor);

    $resource = Mage::getSingleton('core/resource');
    $readConnection = $resource->getConnection('core_read');
    $attributeId = intval(Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', $attribute));
    $tbAttributeOption = $resource->getTableName('eav_attribute_option');
    $tbAttributeOptionValue = $resource->getTableName('eav_attribute_option_value');
    $select = "select t.option_id from $tbAttributeOption as t  
    inner join $tbAttributeOptionValue as j1 
     on j1.option_id = t.option_id where 
    t.attribute_id = $attributeId and j1.value = '$label'";

    $retorno = $readConnection->fetchOne($select);
  
    if(intval($retorno)<1)
    {
        
        createAttribute($db, $attributeId, $atributo_codigo, $label);
        $retorno = $readConnection->fetchOne($select);
    }
    return isset($retorno) ? $retorno : null;
}

function createAttribute($db, $attributeId, $atributo_codigo, $value) {
    $select = $db->select();
    $select->from(array('a' => 'propiedades'));
    $select->where('a.mg_propiedad=?', $atributo_codigo);
    $select->where('a.bf_id=?', $value);

    $propiedad = $db->fetchRow($select);

    if (isset($propiedad['id']) && intval($attributeId) > 0) {
        $option['attribute_id'] = $attributeId; //manufacturer
        $option['value']['any_key_that_resolves_to_zero'][0] = $propiedad['bf_id'];
        $option['value']['any_key_that_resolves_to_zero'][1] = mb_convert_case($propiedad['bf_valor'], MB_CASE_TITLE, "UTF-8");
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $setup->addAttributeOption($option);
    } elseif( intval($attributeId) > 0) {
        $option['attribute_id'] = $attributeId; //manufacturer
        $option['value']['any_key_that_resolves_to_zero'][0] = $value;
        //$option['value']['any_key_that_resolves_to_zero'][1] = mb_convert_case($value, MB_CASE_TITLE, "UTF-8");
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $setup->addAttributeOption($option);
    }else{
        echo $atributo_codigo.'<br>';
    }
    
   
}
