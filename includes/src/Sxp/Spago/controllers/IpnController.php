<?php
/**
 * Sxp
 * Spago
 *
 * @category    Sxp
 * @package     Sxp_Spago
 * @copyright   Copyright (c) 2011 Sxp (http://www.magentoxp.com)
 * @author      Magentoxp (Sxp)Magentoxp Team <support@magentoxp.com>
 */

class Sxp_Spago_IpnController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        if (!$this->getRequest()->isPost()) {
        	$this->response(404);
            return;
        }

        try {
        	$model = Mage::getModel('spago/standard');
            $data = $this->getRequest()->getPost();
            if($data['acc_id'] == $model->getConfigData('id_sp')){
                $id = trim($data['seller_op_id']);
            	switch ($data['status']){
            		case 'A':
            			$this->setOrderStatus($id, Mage_Sales_Model_Order::STATE_PROCESSING, 'MP - '.$data['status_description']);
            			$this->response(200);
            			break;
            		case 'P':
            			$this->setOrderStatus($id, Mage_Sales_Model_Order::STATE_HOLDED, 'MP - '.$data['status_description']);
            			$this->response(200);
            			break;
            		case 'C':
            			$this->setOrderStatus($id, Mage_Sales_Model_Order::STATE_CANCELED, 'MP - '.$data['status_description']);
            			$this->response(200);
            			break;
            		default:
             			$this->response(404);

            			return;
            	}
            }else{
            	$this->response(404);
            	return;
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        $this->response(404);
        return;
    }

    public function setOrderStatus($id, $status, $msg){
    	$order = Mage::getModel('sales/order')->loadByIncrementId($id);
        $order->setState($status, true, $msg);
        $order->save();
        return;
    }


    public function response($code)
    {
    	$this->getResponse()
        ->setHttpResponseCode($code)
        ->setHeader('Pragma', 'public', true)
        ->setHeader('Content-type', 'text/html', true);

        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();

    }
}
