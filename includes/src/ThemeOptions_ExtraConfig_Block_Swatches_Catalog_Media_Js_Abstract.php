<?php
abstract class ThemeOptions_ExtraConfig_Block_Swatches_Catalog_Media_Js_Abstract 
        extends Mage_ConfigurableSwatches_Block_Catalog_Media_Js_Abstract {

    public function getProductImageFallbacks($keepFrame = null) {
        /* @var $helper Mage_ConfigurableSwatches_Helper_Mediafallback */
        $helper = Mage::helper('ExtraConfig/mediafallback');

        $fallbacks = array();

        $products = $this->getProducts();

        if ($keepFrame === null) {
            $listBlock = $this->getLayout()->getBlock('product_list');
            if ($listBlock && $listBlock->getMode() == 'grid') {
                $keepFrame = true;
            } else {
                $keepFrame = false;
            }
        }

        /* @var $product Mage_Catalog_Model_Product */
        foreach ($products as $product) {
            $imageFallback = $helper->getConfigurableImagesFallbackArray($product, $this->_getImageSizes(), $keepFrame);

            $fallbacks[$product->getId()] = array(
                'product' => $product,
                'image_fallback' => $this->_getJsImageFallbackString($imageFallback)
            );
        }

        return $fallbacks;
    }

}
