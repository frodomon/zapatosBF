<?php
/**
 * Magento
 *
 */

class Sxp_Spago_Block_Instrucciones extends Mage_Core_Block_Template
{
    /**
     * @deprecated after 1.4.0.1
     */
    private $_order;

    /**
     * Retrieve identifier of created order
     *
     * @return string
     * @deprecated after 1.4.0.1
     */
    public function getOrderId()
    {
        return $this->_getData('order_id');
    }

}
