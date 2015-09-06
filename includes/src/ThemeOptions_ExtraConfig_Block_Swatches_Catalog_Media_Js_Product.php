<?php

class ThemeOptions_ExtraConfig_Block_Swatches_Catalog_Media_Js_Product
    extends ThemeOptions_ExtraConfig_Block_Swatches_Catalog_Media_Js_Abstract
{
    /**
     * Return array of single product -- current product
     *
     * @return array
     */
    public function getProducts() {
        $product = Mage::registry('product');

        if (!$product) {
            return array();
        }

        return array($product);
    }

    /**
     * Default to base image type
     *
     * @return string
     */
    public function getImageType() {
        $type = parent::getImageType();

        if (empty($type)) {
            $type = Mage_ConfigurableSwatches_Helper_Productimg::MEDIA_IMAGE_TYPE_BASE;
        }

        return $type;
    }

    /**
     * instruct image image type to be loaded
     *
     * @return array
     */
    protected function _getImageSizes() {
        return array("image", "small_image");
    }
}
