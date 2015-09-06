<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Gfgrisales
 * @package    Gfgrisales_Payu
 * @copyright  Copyright (c) 2013 gfranco.info [modified from vnphpexpert.com]
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Sxp_Spago_Block_Standard_Info extends Mage_Payment_Block_Info
{

    protected $_instructions;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('spago/info.phtml');
    }

    public function getTest()
    {
        return "exito";
    }

    public function getOrderId()
    {
        $orderId = Mage::app()->getRequest()->getParam('order_id');
        if($orderId != null || $orderId != ''){
            return  $orderId;
        }
        return false;
    }

    public function getEstadoSeguripago($ordenId)
    {
        $resultado = "";
        if( $ordenId != null || $ordenId != ''){
            $order = Mage::getModel('sales/order')->load($ordenId);
            foreach ($order->getAllStatusHistory() as $i => $comment) {
                $body = $comment->getData('comment');
                $encontrado = strpos($body, "Seguripago");
                if($encontrado){
                    $resultado = $body;
                }
            }
        }

        return $resultado ;
    }

    public function getInstructions()
    {
        if (is_null($this->_instructions)) {
            $this->_convertAdditionalData();
        }
        return $this->_instructions;
    }

    protected function _convertAdditionalData()
    {
        $details = @unserialize($this->getInfo()->getAdditionalData());
        if (is_array($details)) {
            $this->_instructions = isset($details['instructions']) ? (string) $details['instructions'] : '';
        } else {
            $this->_instructions = '';
        }
        return $this;
    }

}
